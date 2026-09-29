<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Core data every site needs, then the site-specific content seeder
     * configured in config/cms.php (CMS_SITE_SEEDER).
     */
    public function run(): void
    {
        $this->call(CoreSeeder::class);

        if ($siteSeeder = config('cms.site_seeder')) {
            $this->call($siteSeeder);
        }
    }
}
