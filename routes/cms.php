<?php

use App\Http\Controllers\Frontend\PageController;
use Illuminate\Support\Facades\Route;

// Hierarchical CMS pages, e.g. /blog/my-post. Must stay the last registered route.
Route::get('/{path}', [PageController::class, 'show'])
    ->where('path', '^(?!admin(/|$)|storage/|build/).+')
    ->name('page.show');
