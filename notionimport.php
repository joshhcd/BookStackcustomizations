<?php

// Configuration
// Path to the exported Notion HTML folder
define('EXPORT_DIR', __DIR__ . '/notion_export');
// Output portable ZIP path
define('OUTPUT_ZIP', __DIR__ . '/bookstack_portable.zip');

// Create a temporary working directory
$tempDir = sys_get_temp_dir() . '/bookstack_' . uniqid();
mkdir("{$tempDir}", 0755, true);
mkdir("{$tempDir}/files", 0755, true);

// Helpers
/**
 * Remove trailing Notion-generated hex ID (16–32 chars) from a name
 */
function stripNotionId(string $name): string {
    return preg_replace('/\s[0-9a-f]{16,32}$/i', '', $name);
}

/**
 * Generate a UUID v4 string
 */
function generateUuid(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x-%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0xffff), mt_rand(0,0x0fff) | 0x4000,
        mt_rand(0,0x3fff) | 0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
    );
}

/**
 * Convert a file path to a clean display name
 */
function sanitizeHtmlName(string $filePath): string {
    return stripNotionId(pathinfo($filePath, PATHINFO_FILENAME));
}

/**
 * Process a Notion-exported HTML file:
 *  - Remove Notion page header <h1> and <header>
 *  - Copy local images into tempDir/files and record metadata
 *  - Rewrite <img> src to just filename
 *  - Rewrite internal .html links to clean BookStack slugs
 *  - Extract inner <body> HTML
 *  - Return a BookStack page array
 */
function processPage(string $filePath, string $tempDir, array &$attachments): array {
    $html = file_get_contents($filePath);
    $doc  = new DOMDocument();
    @$doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Remove Notion-generated headers
    foreach (['h1','header'] as $tag) {
        $nodes = $doc->getElementsByTagName($tag);
        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $node = $nodes->item($i);
            $node->parentNode->removeChild($node);
        }
    }

    $pageImages = [];
    $pageBase   = basename($filePath, '.html');
    $pageDir    = dirname($filePath) . '/' . $pageBase;

    // Handle local <img> tags
    foreach ($doc->getElementsByTagName('img') as $img) {
        $src = $img->getAttribute('src');
        $url = parse_url($src);
        $path = rawurldecode($url['path'] ?? $src);

        // Possible image locations
        $candidates = [
            dirname($filePath) . '/' . $path,
            $pageDir . '/' . $path
        ];
        $asset = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand)) { $asset = realpath($cand); break; }
        }
        if ($asset) {
            $fname = basename($asset);
            if (!isset($attachments[$fname])) {
                $attachments[$fname] = $asset;
                copy($asset, "{$tempDir}/files/{$fname}");
            }
            $img->setAttribute('src', $fname);
            $pageImages[] = [
                'name' => stripNotionId(pathinfo($asset, PATHINFO_FILENAME)),
                'file' => $fname,
                'type' => 'image'
            ];
        }
    }

    // Rewrite internal .html links to slugs
    foreach ($doc->getElementsByTagName('a') as $a) {
        $href = $a->getAttribute('href');
        if (!preg_match('#^https?://#i', $href) && preg_match('/\.html($|[?#])/i', $href)) {
            $slugBase = rawurldecode(parse_url($href, PHP_URL_PATH));
            $slugBase = preg_replace('/\.html$/i', '', basename($slugBase));
            $slugClean = stripNotionId($slugBase);
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $slugClean), '-'));
            $a->setAttribute('href', $slug);
        }
    }

    // Extract <body> inner HTML
    $body = $doc->getElementsByTagName('body')->item(0);
    $inner = '';
    foreach ($body->childNodes as $child) {
        $inner .= $doc->saveHTML($child);
    }

    return [
        'name'        => sanitizeHtmlName($filePath),
        'html'        => $inner,
        'priority'    => 0,
        'attachments' => $pageImages,
        'tags'        => []
    ];
}

// Determine BookStack book and import root
$rootEntries = array_diff(scandir(EXPORT_DIR), ['.', '..']);
$dirs  = array_filter($rootEntries, fn($e) => is_dir(EXPORT_DIR . "/{$e}"));
$htmls = array_filter($rootEntries, fn($e) => is_file(EXPORT_DIR . "/{$e}") && strtolower(pathinfo($e, PATHINFO_EXTENSION)) === 'html');

$baseDir  = EXPORT_DIR;
$bookName = stripNotionId(basename(EXPORT_DIR));
// If export root contains a single folder with matching HTML, drill into it
if (count($dirs) === 1 && count($htmls) > 0) {
    $only = reset($dirs);
    $cand = stripNotionId($only);
    foreach ($htmls as $h) {
        if (stripNotionId(pathinfo($h, PATHINFO_FILENAME)) === $cand) {
            $baseDir  = EXPORT_DIR . '/' . $only;
            $bookName = $cand;
            break;
        }
    }
}

// Collect pages (root HTML) and chapters (subfolders)
$entries = array_diff(scandir($baseDir), ['.', '..']);
$bookPages    = [];
$bookChapters = [];
$attachments  = [];

foreach ($entries as $e) {
    $path = $baseDir . '/' . $e;
    // Root-level pages
    if (is_file($path) && strtolower(pathinfo($e, PATHINFO_EXTENSION)) === 'html') {
        $bookPages[] = processPage($path, $tempDir, $attachments);
        continue;
    }
    // Folders become chapters
    if (is_dir($path)) {
        $chapName = stripNotionId($e);
        $chapPages = [];
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($rii as $f) {
            if ($f->isFile() && strtolower($f->getExtension()) === 'html') {
                $chapPages[] = processPage($f->getRealPath(), $tempDir, $attachments);
            }
        }
        $bookChapters[] = [
            'name'             => $chapName,
            'description_html' => '',
            'priority'         => 0,
            'pages'            => $chapPages,
            'tags'             => []
        ];
    }
}

// Create data.json for BookStack portable import
$data = [
    'exported_at' => date('c'),
    'instance'    => [
        'id'      => generateUuid(),
        'version' => 'v25.02.3'
    ],
    'book'        => [
        'name'             => $bookName,
        'description_html' => '',
        'chapters'         => $bookChapters,
        'pages'            => $bookPages,
        'tags'             => []
    ]
];
file_put_contents("{$tempDir}/data.json", json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

// Create the portable ZIP
$zip = new ZipArchive();
if ($zip->open(OUTPUT_ZIP, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException("Failed to create ZIP: " . OUTPUT_ZIP);
}
// Add manifest
$zip->addFile("{$tempDir}/data.json", 'data.json');
// Add all files under files/
$zip->addEmptyDir('files');
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("{$tempDir}/files", FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($it as $f) {
    $zip->addFile($f->getRealPath(), 'files/' . $f->getFilename());
}
$zip->close();

// Cleanup temporary files
declare(ticks=1);
function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
        $path = "$dir/$item";
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}
rrmdir($tempDir);

echo "BookStack portable ZIP successfully created: " . OUTPUT_ZIP . "\n";
