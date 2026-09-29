<?php

/*
|--------------------------------------------------------------------------
| Website settings schema
|--------------------------------------------------------------------------
|
| Drives the admin "Settings" and "SEO" screens, their validation and the
| generic defaults. Values are stored in the `settings` table as
| "group.key" and read with settings('group.key').
|
| Field types: text, email, url, tel, textarea, color, image, select,
| toggle, page (select of pages), timezone, code.
|
*/

return [

    'branding' => [
        'label' => 'Branding',
        'description' => 'Site identity used across the website and admin panel.',
        'fields' => [
            'site_name' => ['label' => 'Website name', 'type' => 'text', 'rules' => 'required|string|max:120', 'default' => 'My Website'],
            'tagline' => ['label' => 'Tagline', 'type' => 'text', 'rules' => 'nullable|string|max:200', 'default' => ''],
            'logo' => ['label' => 'Logo', 'type' => 'image', 'help' => 'PNG, JPG or WebP. Max 2 MB, up to 2000px wide.', 'rules' => 'image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=2000,max_height=2000'],
            'footer_logo' => ['label' => 'Footer logo', 'type' => 'image', 'help' => 'Optional. Falls back to the main logo.', 'rules' => 'image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=2000,max_height=2000'],
            'admin_logo' => ['label' => 'Admin logo', 'type' => 'image', 'help' => 'Optional. Shown in the admin sidebar and login screen.', 'rules' => 'image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=2000,max_height=2000'],
            'favicon' => ['label' => 'Favicon', 'type' => 'image', 'help' => 'Square PNG, WebP or ICO between 16px and 512px. Max 512 KB.', 'rules' => 'file|mimes:png,webp,ico|max:512|dimensions:ratio=1,min_width=16,max_width=512'],
        ],
    ],

    'theme' => [
        'label' => 'Theme',
        'description' => 'Colours are exposed as CSS variables, so changes apply site-wide instantly.',
        'fields' => [
            'primary_color' => ['label' => 'Primary colour', 'type' => 'color', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'default' => '#0b5cab'],
            'secondary_color' => ['label' => 'Secondary colour', 'type' => 'color', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'default' => '#0f172a'],
            'accent_color' => ['label' => 'Accent colour', 'type' => 'color', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'default' => '#f59e0b'],
            'button_color' => ['label' => 'Button colour', 'type' => 'color', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'default' => '#0b5cab'],
            'text_color' => ['label' => 'Text colour', 'type' => 'color', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'default' => '#1e293b'],
            'background_color' => ['label' => 'Background colour', 'type' => 'color', 'rules' => 'required|regex:/^#[0-9a-fA-F]{6}$/', 'default' => '#ffffff'],
        ],
    ],

    'general' => [
        'label' => 'General',
        'description' => 'Core website behaviour and footer information.',
        'fields' => [
            'site_email' => ['label' => 'Website email', 'type' => 'email', 'rules' => 'nullable|email|max:150', 'default' => ''],
            'phone' => ['label' => 'Phone number', 'type' => 'tel', 'rules' => 'nullable|string|max:40', 'default' => ''],
            'address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => 'nullable|string|max:500', 'default' => ''],
            'copyright_text' => ['label' => 'Copyright text', 'type' => 'text', 'help' => 'Use {year} and {site} as placeholders.', 'rules' => 'nullable|string|max:255', 'default' => '© {year} {site}. All rights reserved.'],
            'disclaimer' => ['label' => 'Site disclaimer', 'type' => 'textarea', 'help' => 'Shown in the footer and the top notice bar. Leave empty to hide.', 'rules' => 'nullable|string|max:1000', 'default' => ''],
            'timezone' => ['label' => 'Timezone', 'type' => 'timezone', 'rules' => 'required|timezone', 'default' => 'UTC'],
            'default_language' => ['label' => 'Default language', 'type' => 'select', 'options' => ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese', 'it' => 'Italian'], 'rules' => 'required|in:en,es,fr,de,pt,it', 'default' => 'en'],
            'home_page_id' => ['label' => 'Home page', 'type' => 'page', 'rules' => 'nullable|integer|exists:pages,id', 'default' => null],
            'blog_page_id' => ['label' => 'Blog index page', 'type' => 'page', 'help' => 'New blog posts are placed under this page.', 'rules' => 'nullable|integer|exists:pages,id', 'default' => null],
            'hotels_page_id' => ['label' => 'Hotels index page', 'type' => 'page', 'help' => 'New hotels are placed under this page.', 'rules' => 'nullable|integer|exists:pages,id', 'default' => null],
        ],
    ],

    'contact' => [
        'label' => 'Contact',
        'description' => 'Shown on the contact page, footer and call-to-action blocks.',
        'fields' => [
            'contact_email' => ['label' => 'Contact email', 'type' => 'email', 'help' => 'New enquiries are emailed here when notifications are on.', 'rules' => 'nullable|email|max:150', 'default' => ''],
            'support_email' => ['label' => 'Support email', 'type' => 'email', 'rules' => 'nullable|email|max:150', 'default' => ''],
            'phone' => ['label' => 'Phone', 'type' => 'tel', 'rules' => 'nullable|string|max:40', 'default' => ''],
            'phone_label' => ['label' => 'Phone label', 'type' => 'text', 'help' => 'E.g. "Toll-free" — shown next to call buttons.', 'rules' => 'nullable|string|max:60', 'default' => ''],
            'whatsapp' => ['label' => 'WhatsApp number', 'type' => 'tel', 'help' => 'International format, e.g. +15551234567.', 'rules' => 'nullable|string|max:40', 'default' => ''],
            'business_address' => ['label' => 'Business address', 'type' => 'textarea', 'rules' => 'nullable|string|max:500', 'default' => ''],
            'map_embed_url' => ['label' => 'Map embed URL', 'type' => 'url', 'help' => 'Google Maps "embed" URL for the contact page.', 'rules' => 'nullable|url:https|max:1000', 'default' => ''],
            'notify_on_enquiry' => ['label' => 'Email me new enquiries', 'type' => 'toggle', 'rules' => 'boolean', 'default' => '1'],
            'show_call_bar' => ['label' => 'Show sticky call button on mobile', 'type' => 'toggle', 'rules' => 'boolean', 'default' => '0'],
        ],
    ],

    'social' => [
        'label' => 'Social media',
        'description' => 'Leave a field empty to hide its icon.',
        'fields' => [
            'facebook' => ['label' => 'Facebook', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'default' => ''],
            'instagram' => ['label' => 'Instagram', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'default' => ''],
            'twitter' => ['label' => 'Twitter / X', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'default' => ''],
            'linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'default' => ''],
            'youtube' => ['label' => 'YouTube', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'default' => ''],
            'whatsapp' => ['label' => 'WhatsApp link', 'type' => 'url', 'help' => 'E.g. https://wa.me/15551234567', 'rules' => 'nullable|url|max:255', 'default' => ''],
        ],
    ],

    'integrations' => [
        'label' => 'Integrations',
        'description' => 'API keys are configured in the .env file. These options control how the data is used.',
        'fields' => [
            'airport_iata' => ['label' => 'Airport IATA code', 'type' => 'text', 'help' => 'Used by the flight board, disruptions and TSA wait-time widgets.', 'rules' => 'nullable|alpha|size:3', 'default' => ''],
            'airport_name' => ['label' => 'Airport name', 'type' => 'text', 'rules' => 'nullable|string|max:120', 'default' => ''],
            'arrivals_page_id' => ['label' => 'Arrivals page', 'type' => 'page', 'rules' => 'nullable|integer|exists:pages,id', 'default' => null],
            'departures_page_id' => ['label' => 'Departures page', 'type' => 'page', 'rules' => 'nullable|integer|exists:pages,id', 'default' => null],
        ],
    ],

    // Rendered on the dedicated SEO screen rather than the Settings tabs.
    'seo' => [
        'label' => 'SEO',
        'description' => 'Defaults used when a page does not define its own values.',
        'screen' => 'seo',
        'fields' => [
            'meta_title' => ['label' => 'Default meta title', 'type' => 'text', 'rules' => 'nullable|string|max:255', 'default' => ''],
            'title_suffix' => ['label' => 'Title suffix', 'type' => 'text', 'help' => 'Appended to page titles, e.g. " | My Site". Leave empty for none.', 'rules' => 'nullable|string|max:80', 'default' => ''],
            'meta_description' => ['label' => 'Default meta description', 'type' => 'textarea', 'rules' => 'nullable|string|max:500', 'default' => ''],
            'meta_keywords' => ['label' => 'Default keywords', 'type' => 'text', 'rules' => 'nullable|string|max:500', 'default' => ''],
            'og_image' => ['label' => 'Default Open Graph image', 'type' => 'image', 'help' => '1200×630 recommended. Max 2 MB.', 'rules' => 'image|mimes:png,jpg,jpeg,webp|max:2048'],
            'google_analytics_id' => ['label' => 'Google Analytics ID', 'type' => 'text', 'help' => 'Measurement ID, e.g. G-XXXXXXXXXX.', 'rules' => ['nullable', 'regex:/^(G|UA|GT|AW)-[A-Z0-9-]+$/i', 'max:40'], 'default' => ''],
            'google_site_verification' => ['label' => 'Google Search Console verification', 'type' => 'text', 'help' => 'Only the content value of the meta tag.', 'rules' => 'nullable|string|max:120', 'default' => ''],
            'bing_site_verification' => ['label' => 'Bing verification', 'type' => 'text', 'rules' => 'nullable|string|max:120', 'default' => ''],
            'indexing_enabled' => ['label' => 'Allow search engines to index this site', 'type' => 'toggle', 'rules' => 'boolean', 'default' => '1'],
            'robots_txt' => ['label' => 'Extra robots.txt rules', 'type' => 'code', 'help' => 'Appended after the generated rules. The sitemap line is added automatically.', 'rules' => 'nullable|string|max:5000', 'default' => ''],
        ],
    ],
];
