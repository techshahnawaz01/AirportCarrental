<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Site-agnostic defaults: the first administrator, default settings and empty menus.
 */
class CoreSeeder extends Seeder
{
    public function run(SettingsService $settings): void
    {
        $password = config('cms.admin.password') ?: Str::password(16);

        User::updateOrCreate(
            ['email' => config('cms.admin.email')],
            ['name' => config('cms.admin.name'), 'password' => $password, 'role' => User::ROLE_ADMIN, 'is_active' => true],
        );

        if (! config('cms.admin.password')) {
            $this->command?->warn('ADMIN_PASSWORD is not set. Generated password for '.config('cms.admin.email').': '.$password);
        }

        $settings->set(array_filter($settings->defaults(), fn ($value) => $value !== null));

        foreach (config('cms.menu_locations') as $location => $name) {
            Menu::firstOrCreate(['location' => $location], ['name' => $name]);
        }
    }
}
