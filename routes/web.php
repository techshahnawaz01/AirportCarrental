<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Frontend;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public system routes
|--------------------------------------------------------------------------
| CMS pages are resolved by the catch-all in routes/cms.php.
*/

Route::get('/', [Frontend\PageController::class, 'home'])->name('home');
Route::get('/search', Frontend\SearchController::class)->middleware('throttle:search')->name('search');
Route::get('/sitemap.xml', [Frontend\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [Frontend\SeoController::class, 'robots'])->name('robots');

Route::middleware('throttle:forms')->group(function () {
    Route::post('/enquiries', [Frontend\EnquiryController::class, 'store'])->name('enquiries.store');
    Route::post('/subscribe', [Frontend\SubscriberController::class, 'store'])->name('subscribers.store');
    Route::post('/pages/{page}/comments', [Frontend\CommentController::class, 'store'])->name('comments.store');
});

Route::prefix('widgets')->name('widgets.')->middleware('throttle:search')->group(function () {
    Route::get('/flights/{direction}', [Frontend\FlightController::class, 'board'])
        ->whereIn('direction', ['arrivals', 'departures'])->name('flights');
    Route::get('/disruptions', [Frontend\FlightController::class, 'disruptions'])->name('disruptions');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\Auth\LoginController::class, 'create'])->name('login');
        Route::post('login', [Admin\Auth\LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
        Route::get('forgot-password', [Admin\Auth\PasswordResetController::class, 'request'])->name('password.request');
        Route::post('forgot-password', [Admin\Auth\PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
        Route::get('reset-password/{token}', [Admin\Auth\PasswordResetController::class, 'edit'])->name('password.reset');
        Route::post('reset-password', [Admin\Auth\PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
    });

    Route::middleware(['auth', 'active', 'can:access-admin'])->group(function () {
        Route::post('logout', [Admin\Auth\LoginController::class, 'destroy'])->name('logout');

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Content
        Route::patch('pages/{id}/restore', [Admin\PageController::class, 'restore'])->name('pages.restore');
        Route::delete('pages/{id}/force', [Admin\PageController::class, 'forceDelete'])->name('pages.force-delete');
        Route::patch('pages/{page}/toggle', [Admin\PageController::class, 'toggle'])->name('pages.toggle');
        Route::resource('pages', Admin\PageController::class)->except('show');

        Route::get('media/picker', [Admin\MediaController::class, 'picker'])->name('media.picker');
        Route::resource('media', Admin\MediaController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['media' => 'media']);

        Route::patch('comments/{comment}/toggle', [Admin\CommentController::class, 'toggle'])->name('comments.toggle');
        Route::resource('comments', Admin\CommentController::class)->only(['index', 'destroy']);

        // Engagement
        Route::patch('enquiries/{enquiry}/read', [Admin\EnquiryController::class, 'toggleRead'])->name('enquiries.read');
        Route::resource('enquiries', Admin\EnquiryController::class)->only(['index', 'show', 'destroy']);
        Route::resource('subscribers', Admin\SubscriberController::class)->only(['index', 'destroy']);
        Route::get('subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');

        // Account
        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [Admin\ProfileController::class, 'password'])->name('profile.password');

        // Site configuration (administrators only)
        Route::middleware('can:manage-site')->group(function () {
            Route::get('settings/{group?}', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings/{group}', [Admin\SettingController::class, 'update'])->name('settings.update');
            Route::delete('settings/image/{key}', [Admin\SettingController::class, 'destroyImage'])
                ->where('key', '[a-z_]+\.[a-z_]+')->name('settings.image.destroy');

            Route::get('seo', [Admin\SeoController::class, 'edit'])->name('seo.edit');
            Route::resource('redirects', Admin\RedirectController::class)->only(['store', 'update', 'destroy']);

            Route::resource('menus', Admin\MenuController::class)->only(['index', 'store', 'edit', 'destroy']);
            Route::post('menus/{menu}/items', [Admin\MenuItemController::class, 'store'])->name('menus.items.store');
            Route::put('menus/{menu}/items/{item}', [Admin\MenuItemController::class, 'update'])->scopeBindings()->name('menus.items.update');
            Route::delete('menus/{menu}/items/{item}', [Admin\MenuItemController::class, 'destroy'])->scopeBindings()->name('menus.items.destroy');
            Route::post('menus/{menu}/reorder', [Admin\MenuItemController::class, 'reorder'])->name('menus.items.reorder');

            Route::patch('users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
            Route::resource('users', Admin\UserController::class)->except('show');

            Route::get('activity', Admin\ActivityController::class)->name('activity');
            Route::post('cache/clear', [Admin\DashboardController::class, 'clearCache'])->name('cache.clear');
        });
    });
});
