<?php

use App\Shortcodes\CallCtaShortcode;
use App\Shortcodes\CardShortcode;
use App\Shortcodes\ChildPagesShortcode;
use App\Shortcodes\ContactFormShortcode;
use App\Shortcodes\CtaShortcode;
use App\Shortcodes\FaqsShortcode;
use App\Shortcodes\FlightBoardShortcode;
use App\Shortcodes\FlightDisruptionsShortcode;
use App\Shortcodes\FlightWidgetShortcode;
use App\Shortcodes\LatestPostsShortcode;
use App\Shortcodes\LinkCardsShortcode;
use App\Shortcodes\MediaTextShortcode;
use App\Shortcodes\WaitTimesShortcode;
use Database\Seeders\Sites\MiamiAirportSeeder;

/*
|--------------------------------------------------------------------------
| CMS foundation configuration
|--------------------------------------------------------------------------
|
| Everything in this file is site-agnostic. Branding, contact details,
| colours, pages and navigation live in the database and are managed from
| the admin panel. Change this file only to change *capabilities*.
|
*/

return [

    /*
    | Content types. Each type gets its own admin listing and a default
    | template. Types are stored on pages.type.
    */
    'page_types' => [
        'page' => [
            'label' => 'Pages',
            'singular' => 'Page',
            'icon' => 'document',
            'default_template' => 'default',
            'comments' => false,
        ],
        'post' => [
            'label' => 'Blog Posts',
            'singular' => 'Post',
            'icon' => 'newspaper',
            'default_template' => 'post',
            'comments' => true,
            // Setting holding the page id posts are nested under by default.
            'parent_setting' => 'general.blog_page_id',
        ],
        'hotel' => [
            'label' => 'Hotels',
            'singular' => 'Hotel',
            'icon' => 'building',
            'default_template' => 'hotel',
            'comments' => false,
            'parent_setting' => 'general.hotels_page_id',
        ],
    ],

    /*
    | Front-end templates available when editing a page.
    | Key = view name under resources/views/frontend/templates.
    */
    'templates' => [
        'default' => 'Standard page (content + sidebar)',
        'full-width' => 'Full width',
        'home' => 'Home page',
        'listing' => 'Listing (shows child pages)',
        'post' => 'Blog post',
        'hotel' => 'Hotel detail',
        'contact' => 'Contact page',
    ],

    /*
    | Navigation menu locations rendered by the theme.
    */
    'menu_locations' => [
        'header' => 'Header navigation',
        'footer' => 'Footer columns',
        'legal' => 'Footer legal links',
        'quick_links' => 'Home quick links (icon tiles)',
        'featured' => 'Featured link cards (with images)',
    ],

    /*
    | Icons available for menu items / quick link tiles.
    | Key = partial in resources/views/components/icon.blade.php.
    */
    'icons' => [
        'plane', 'plane-departure', 'plane-arrival', 'parking', 'shield', 'walking',
        'taxi', 'car', 'hotel', 'map', 'clock', 'luggage', 'wifi', 'utensils',
        'shopping', 'info', 'phone', 'mail', 'wheelchair', 'search', 'globe', 'star',
    ],

    'pagination' => [
        'frontend' => 12,
        'admin' => 15,
    ],

    'uploads' => [
        // Kilobytes
        'max_image_size' => 5120,
        'image_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        // Longest edge in pixels. Larger uploads are downscaled when GD is available.
        'max_dimension' => 2400,
        'directory' => 'media',
    ],

    /*
    | Content shortcodes. Editors can place e.g. [contact_form] in page content.
    | Add your own by implementing App\Shortcodes\Shortcode and registering it here.
    */
    'shortcodes' => [
        'contact_form' => ContactFormShortcode::class,
        'faqs' => FaqsShortcode::class,
        'child_pages' => ChildPagesShortcode::class,
        'latest_posts' => LatestPostsShortcode::class,
        'link_cards' => LinkCardsShortcode::class,
        'cta' => CtaShortcode::class,
        'call_cta' => CallCtaShortcode::class,
        'media_text' => MediaTextShortcode::class,
        'card' => CardShortcode::class,
        'flight_widget' => FlightWidgetShortcode::class,
        'flight_board' => FlightBoardShortcode::class,
        'flight_disruptions' => FlightDisruptionsShortcode::class,
        'wait_times' => WaitTimesShortcode::class,
    ],

    /*
    | Optional third-party integrations. Keys live in .env only.
    */
    'integrations' => [
        'aviationstack' => [
            'keys' => array_values(array_filter(array_map('trim', explode(',', (string) env('AVIATIONSTACK_KEYS', ''))))),
            'monthly_limit_per_key' => 100,
            'delay_threshold' => 10, // minutes
            'disruptions_ttl' => 6 * 3600,
        ],
        'tsa_wait_times' => [
            'key' => env('TSA_WAIT_TIMES_KEY'),
            'ttl' => 900,
        ],
    ],

    /*
    | Default admin created by the seeder on a fresh install.
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    | Site content seeder used by `php artisan migrate:fresh --seed`.
    | Point this at your own seeder class to bootstrap a different website.
    */
    'site_seeder' => env('CMS_SITE_SEEDER', MiamiAirportSeeder::class),
];
