<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use App\Services\MenuService;
use App\Services\SettingsService;
use App\Support\FaqRegistry;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(MenuService::class);
        $this->app->scoped(FaqRegistry::class);
    }

    public function boot(): void
    {
        Paginator::defaultView('components.pagination');

        // Access control: every active staff account can use the admin; site-wide configuration is admin-only.
        Gate::define('access-admin', fn (User $user) => $user->is_active && array_key_exists($user->role, User::ROLES));
        Gate::define('manage-site', fn (User $user) => $user->is_active && $user->isAdmin());

        ResetPassword::createUrlUsing(fn ($user, string $token) => route('admin.password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]));

        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        // Menus cache page URLs, so any page or menu change clears them.
        foreach ([Page::class, Menu::class, MenuItem::class] as $model) {
            $model::saved(fn () => app(MenuService::class)->flush());
            $model::deleted(fn () => app(MenuService::class)->flush());
        }

        Blade::if('setting', fn (string $key) => filled(settings($key)));
    }
}
