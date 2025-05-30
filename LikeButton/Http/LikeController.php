<?php
namespace BookStack\Likeable\Http;

use BookStack\Http\Controller;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function count($type, $identifier)
    {
        $modelClass = $type === 'pages'
            ? \BookStack\Entities\Models\Page::class
            : \BookStack\Entities\Models\Book::class;

        // Numeric? find by ID, otherwise by slug.
        $item = is_numeric($identifier)
            ? $modelClass::findOrFail($identifier)
            : $modelClass::where('slug', $identifier)->firstOrFail();

        return response()->json(['count' => $item->likes()->count()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:pages,books',
            'item_id' => 'required|string',
        ]);
    
        // Resolve model class & lookup by slug or numeric ID
        $modelClass = $request->type === 'pages'
            ? \BookStack\Entities\Models\Page::class
            : \BookStack\Entities\Models\Book::class;
    
        $identifier = $request->item_id;
        $item = is_numeric($identifier)
              ? $modelClass::findOrFail($identifier)
              : $modelClass::where('slug', $identifier)->firstOrFail();
    
        // Check for an existing like
        $existing = $item->likes()->where('user_id', auth()->id())->first();
    
        if ($existing) {
            // If found, remove it (unlike)
            $existing->delete();
            $removed = true;
        } else {
            // Otherwise create it (like)
            $item->likes()->create(['user_id' => auth()->id()]);
            $removed = false;
        }
    
        return response()->json(['ok' => true, 'removed' => $removed]);
    }

}
