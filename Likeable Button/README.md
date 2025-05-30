```markdown
# BookStack “Likeable” Plugin

Add a native “Like” 👍 button to all Books and Pages in BookStack, with real-time counts and Like/Unlike toggling.

---

## 📋 Requirements

- **BookStack v25.02.5+**  
- PHP 8+, Composer, Artisan CLI  
- Write access to your BookStack root (e.g. `public_html/bookstack`)  
- `unzip` (or control-panel file manager)  

---

## 🚀 Installation

Follow these steps **in order**. Copy & paste directly into your GitHub README.

### 1. Download & Extract

Upload `Likeable-Button.zip` to your BookStack root and unzip:

```bash
cd ~/public_html/bookstack
unzip Likeable-Button.zip -d .
```

You should now have:

```
<bookstack-root>/
├── likeable/            ← plugin package
└── themes/likeable/     ← theme overrides
```

### 2. Register as a Composer “Path” Package

Edit **`composer.json`** (in your BookStack root) and **add** **above** the `"require"` section:

```jsonc
"repositories": [
  {
    "type": "path",
    "url": "likeable"
  }
],
```

Then install:

```bash
composer require yoshee08/bookstack-likeable:@dev
```

### 3. Publish Assets & Run Migrations

```bash
# Publish JS to public/vendor/likeable/js
php artisan vendor:publish --tag=likeable-assets

# Create the `likes` table
php artisan migrate
```

### 4. Add  Relations

## Add the provider
Add to `app/config/app.php`
```blade

    'providers' => ServiceProvider::defaultProviders()->merge([
        // Third party service providers
        ............
        BookStack\Likeable\LikeServiceProvider::class,
        ............
```
## Updating the Tri‐Layout Sidebar Partial

Add to `tri-start.blade.php` (or whichever partial you used for the sidebar) the following snippet, replacing the <aside> element. This ensures your Like button appears in the tri‐layout aside.

1. **Open**  
   `resources/views/layouts/tri.blade.php`

2. **Replace** **everything** in that file with **this**:

   ```blade
   <aside class="tri-layout-right-contents">
     @yield('right')

     <div class="mb-4">
       @if(isset($page))
         {{-- Aside Like button on Page --}}
         @include('likeable::components.like-button', [
           'type' => 'pages',
           'id'   => $page->slug
         ])
       @elseif(isset($book))
         {{-- Aside Like button on Book --}}
         @include('likeable::components.like-button', [
           'type' => 'books',
           'id'   => $book->slug
         ])
       @endif
     </div>
   </aside>


### 5. Enable the Custom Theme

In your **`.env`** file:

```dotenv
APP_THEME=likeable
```

(BookStack will now load `themes/likeable/layouts/parts/` overrides.)

### 6. Clear Caches & Verify

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

