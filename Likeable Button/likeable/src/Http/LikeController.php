<?php
namespace BookStack\Likeable\Http;

use App\Http\Controllers\Controller;
use BookStack\Likeable\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function count($type, $id)
    {
        $model = $type === 'pages'
               ? \BookStack\Page::class
               : \BookStack\Book::class;

        $item = $model::findOrFail($id);
        return response()->json(['count' => $item->likes()->count()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:pages,books',
            'item_id' => 'required|integer',
        ]);

        $model = $request->type === 'pages'
               ? \BookStack\Page::class
               : \BookStack\Book::class;

        $item = $model::findOrFail($request->item_id);

        $item->likes()
             ->where('user_id', auth()->id())
             ->first()
             ?: $item->likes()->create(['user_id' => auth()->id()]);

        return response()->json(['ok' => true]);
    }
}
