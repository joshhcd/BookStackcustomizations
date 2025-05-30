<?php
use Illuminate\Support\Facades\Route;
use BookStack\Likeable\Http\LikeController;

Route::group([
  'prefix'     => 'likes',
  'middleware' => ['web', 'auth'],
], function() {
    Route::post('/', [LikeController::class, 'store'])
         ->name('likeable.store');
    Route::get('{type}/{id}', [LikeController::class, 'count'])
         ->name('likeable.count');
});
