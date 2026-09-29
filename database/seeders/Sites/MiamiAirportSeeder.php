<?php

namespace Database\Seeders\Sites;

use App\Models\Page;
use App\Models\Redirect;

/**
 * Content for the Miami International Airport guide (miamiairportmia-info.com).
 *
 * This is the only Miami-specific code in the project. To launch another
 * website, copy this class, change the content and point CMS_SITE_SEEDER at it.
 * The remaining pages of the live site can be pulled in with:
 *   php artisan cms:import-sitemap https://miamiairportmia-info.com/sitemap.xml
 */
class MiamiAirportSeeder extends SiteSeeder
{
    protected string $assets = 'miami-assets';

    public function run(): void
    {
        $this->seedSettings();
        $this->seedPages();
        $this->seedMenus();

        // Carried over from the legacy .htaccess.
        Redirect::updateOrCreate(['from_path' => 'wait-times'], ['to_url' => '/wait-time', 'status_code' => 301]);
    }

    private function seedSettings(): void
    {
        $this->settings([
            'branding.site_name' => 'MiamiAirportMIA-Info',
            'branding.tagline' => 'Your independent guide to Miami International Airport (MIA)',
            'branding.logo' => $this->image('logo.webp', 'Miami Airport MIA Info', 'branding')->path,
            'branding.favicon' => $this->image('favicon.png', 'Favicon', 'branding')->path,

            'theme.primary_color' => '#06519c',
            'theme.secondary_color' => '#0b1f3a',
            'theme.accent_color' => '#ffde66',
            'theme.button_color' => '#06519c',
            'theme.text_color' => '#1e293b',
            'theme.background_color' => '#ffffff',

            'general.site_email' => 'info@miamiairportmia-info.com',
            'general.copyright_text' => '© {year} {site} | All Rights Reserved. (This is not the official airport website)',
            'general.disclaimer' => 'This website is not affiliated with, endorsed by, or authorized by Miami International Airport (MIA). All trademarks and service marks are the property of their respective owners. This website does not engage in fraud, phishing, social engineering, or any malicious or deceptive activity. Any claims to the contrary are inaccurate.',
            'general.timezone' => 'America/New_York',
            'general.default_language' => 'en',

            'contact.contact_email' => 'info@miamiairportmia-info.com',
            'contact.support_email' => 'info@miamiairportmia-info.com',
            'contact.phone' => '+1-833-582-2357',
            'contact.phone_label' => 'Toll-free',
            'contact.show_call_bar' => 1,

            'seo.meta_title' => 'Miami International Airport (MIA) – Flight Info, Terminals, Parking & Services',
            'seo.meta_description' => 'Explore Miami International Airport (MIA) with real-time flight updates, terminal maps, parking info, transportation options, and traveler services.',
            'seo.meta_keywords' => 'Miami International Airport, MIA, flight information, airport amenities, transportation',
            'seo.og_image' => $this->image('hero.webp', 'Miami International Airport')->path,
            'seo.google_analytics_id' => 'G-2ME77TZFLM',
            'seo.google_site_verification' => 'I1s0J6_6CPFAyygWoCnN0usDut_BAM5VmtntKtRl7_Y',
            'seo.bing_site_verification' => '8EB56BE3D839F56214ECC0EEC7687455',
            'seo.robots_txt' => "Disallow: /*?query=\nDisallow: /*?q=",

            'integrations.airport_iata' => 'MIA',
            'integrations.airport_name' => 'Miami International Airport',
        ]);
    }

    private function seedPages(): void
    {
        $terminal = $this->image('terminal.webp', 'Inside Miami International Airport');
        $skyline = $this->image('miami-skyline.webp', 'Miami skyline');
        $ctaBackground = $this->image('cta-background.webp', 'Miami International Airport concourse');

        $home = $this->page([
            'slug' => 'home',
            'title' => 'Miami International Airport',
            'template' => 'home',
            'excerpt' => 'Live flights, terminals, parking, ground transportation and practical travel tips for MIA — all in one place.',
            'meta_title' => 'Miami International Airport (MIA) – Flight Info, Terminals, Parking & Services',
            'meta_description' => 'Explore Miami International Airport (MIA) with real-time flight updates, terminal maps, parking info, transportation options, and traveler services.',
            'featured_image_id' => $this->image('hero.webp', 'Miami International Airport')->id,
            'sitemap_priority' => 1.0,
            'content' => <<<HTML
[flight_widget overlap="true" title="Flight info"]
[link_cards menu="quick_links" style="icons"]
[media_text image="{$terminal->path}" title="Miami International Airport" heading="h2"]
<p>Miami International Airport, often called MIA, is a very busy airport in Florida. It is the biggest airport in Florida and was expected to welcome more than 52 million travellers in 2023. MIA serves the most destinations in Latin America and the Caribbean of any U.S. airport. It is also the main port of entry for those travelling south from the United States. The airport is also a major center for cargo, handling more international goods than any other airport in the U.S.</p>
<p>Inside the MIA airport, you will find more than 120 retail shops, over 110 eateries, and even two relaxing spas. If you are in a hurry, you can use the MIA2Go app to order food in advance. The Skytrain inside the terminal, the MIA Mover shuttle to the transportation hub, and the e-Train that links parts of Concourse E. MIA runs all day and night, every day of the year. It also offers free Wi-Fi and many services to help all types of travellers enjoy their journey.</p>
<p>From business to pleasure travel, this website offers you everything you need, including practical tips to ensure you have a wonderful and easy trip to Miami.</p>
[/media_text]
[wait_times title="Security wait times at MIA"]
[link_cards menu="featured" title="Important insights before you fly" subtitle="Special services, parking and everything in between."]
[media_text image="{$skyline->path}" title="History of Miami International Airport" position="right"]
<p>Miami International Airport began as Pan American Field, operated by Pan American Airways. In 1946, Dade County seized control and changed the name to Miami International Airport. The former Pan Am terminal was modified and formally dedicated in January 1950.</p>
<p>A separate new or rebuilt airport opened on 20th Street on December 10, 1959, but all commercial flights were moved unchanged from the old airport to the new airport upon the new airport's opening. Miami Airport grew throughout the late 1940s and 1950s, becoming the world's largest commercial air terminal by the end of that time.</p>
<p>Over time, MIA has become one of the largest international airports in the world, and it has been the U.S. leader for international freight and one of its top destinations for international passenger traffic.</p>
[/media_text]
[cta title="Need help while navigating?" text="Locate your entry and exit gates, baggage claim belts, dining options, lounges, shops and more with MIA’s interactive map." button="Interactive terminal maps" url="/map" image="{$ctaBackground->path}"]
[latest_posts title="Latest from the blog" limit="5"]
HTML,
        ], [
            ['Where is Miami International Airport located?', 'The physical address for the airport is 2100 NW 42nd Ave, Miami, FL 33142, located about 8 miles northwest of Downtown Miami, Florida.'],
            ['Is Miami International Airport open 24 hours?', 'Yes, MIA operates 24/7, though airline counters and security checkpoints follow specific schedules.'],
            ['What is the code for Miami International Airport?', 'The airport code is MIA.'],
            ['Does Miami International Airport handle international flights?', 'MIA serves as a major international hub, with a strong focus on flights connecting the U.S. to Latin America and the Caribbean.'],
            ['How busy is Miami International Airport?', 'MIA ranks among the busiest airports in the United States, handling more than 52 million travellers each year.'],
            ['How can I contact the MIA airport for general information?', "You can contact Miami International Airport's general information line at 305-876-7000. They also have a toll-free number, 1-800-TALK MIA (800-825-5642)."],
        ]);

        $arrivals = $this->page([
            'slug' => 'flight-arrivals',
            'title' => 'Miami Airport Arrivals',
            'template' => 'full-width',
            'excerpt' => 'Live arrival times, terminals, gates and delays for flights landing at Miami International Airport.',
            'featured_image_id' => $this->image('baggage-hero.webp', 'Arrivals hall at MIA')->id,
            'content' => '<p>Track today’s arrivals at Miami International Airport. Use the filters to find your flight by airline, status or terminal, or search by flight number or origin city.</p>[flight_board type="arrivals"][call_cta title="Flight delayed or cancelled?" text="Get instant help with rebooking, refunds and cancellations."]',
        ]);

        $departures = $this->page([
            'slug' => 'flight-departures',
            'title' => 'Miami Airport Departures',
            'template' => 'full-width',
            'excerpt' => 'Live departure times, terminals, gates and delays for flights leaving Miami International Airport.',
            'featured_image_id' => $this->image('hero.webp')->id,
            'content' => '<p>Check today’s departures from Miami International Airport. Filter by airline, status or terminal, or search by flight number or destination.</p>[flight_board type="departures"][call_cta title="Flight boarding and still waiting in line?" text="Get instant help with flight changes, refunds and cancellations."]',
        ]);

        $this->page([
            'slug' => 'flights',
            'title' => 'Flights',
            'template' => 'listing',
            'excerpt' => 'Flight status, routes and travel guides for Miami International Airport.',
            'content' => '',
        ]);

        $this->page([
            'parent' => 'flights',
            'slug' => 'delays-and-cancellations',
            'title' => 'MIA Flight Delays and Cancellations',
            'template' => 'full-width',
            'excerpt' => 'Today’s delayed and cancelled flights at Miami International Airport, updated throughout the day.',
            'content' => '<p>See which arrivals and departures at MIA are delayed by more than 10 minutes or cancelled today. Switch between arrivals and departures below.</p>[flight_disruptions][call_cta title="Stuck with a delay or cancellation?" text="Our travel specialists can help you rebook or find alternatives."]',
        ]);

        $this->page([
            'slug' => 'wait-time',
            'title' => 'Miami Airport Security Wait Times',
            'template' => 'full-width',
            'excerpt' => 'Current TSA security wait times, PreCheck lanes and hourly estimates at MIA.',
            'content' => '<p>Security wait times at Miami International Airport change throughout the day. Check the current estimate and hourly forecast below, and allow extra time during morning and evening peaks.</p>[wait_times]',
        ]);

        $this->page([
            'slug' => 'transportation',
            'title' => 'Miami Airport Transportation',
            'template' => 'full-width',
            'excerpt' => 'Metrorail, Tri-Rail, buses, shuttles, taxis, rideshare, rental cars and Brightline to and from MIA.',
            'featured_image_id' => $this->image('transportation-hero.webp', 'Miami airport transportation')->id,
            'content' => $this->transportationContent(),
        ]);

        $this->page([
            'slug' => 'blog',
            'title' => 'Blog',
            'template' => 'magazine',
            'excerpt' => 'Travel tips, airport guides and Miami inspiration.',
            'content' => '',
        ]);

        $hotels = $this->page([
            'slug' => 'hotels',
            'title' => 'Hotels near Miami Airport',
            'template' => 'listing',
            'excerpt' => 'Hotels at and around Miami International Airport, with rooms, amenities and directions.',
            'content' => '<p>Whether you have an early flight, a long layover or a late arrival, these hotels near Miami International Airport make it easy to rest close to the terminal.</p>',
        ]);

        $this->page([
            'slug' => 'contact-us',
            'title' => 'Contact Us',
            'template' => 'contact',
            'excerpt' => 'Questions about this website or your trip through MIA? Send us a message.',
            'content' => '<p>We usually reply within one business day. For flight-specific questions such as schedule changes or refunds, please contact your airline directly.</p>',
        ]);

        $this->page([
            'slug' => 'privacy-policy',
            'title' => 'Privacy Policy',
            'template' => 'full-width',
            'noindex' => false,
            'sitemap_priority' => 0.3,
            'content' => $this->privacyContent(),
        ]);

        $this->page([
            'slug' => 'disclaimer',
            'title' => 'Disclaimer',
            'template' => 'full-width',
            'sitemap_priority' => 0.3,
            'content' => '<p>Miamiairport-mia is an external independent website, and the content we post here is not regulated or confirmed by the airport authorities. We are an outside source providing information about Miami and Miami International Airport. Any advertisement for commercial services, products, or processes is not affiliated with Miami Airport officials.</p><p>The name, logos, graphics, trade marks, and even copyrights, solely belong to the Miami International Airport rightfully. It has been used at this site for descriptive goals. User must agree to the terms and conditions to access the website.</p><p>As per our research, the information we deliver is mentioned on the official web page of this particular airport. Our research team always tries to provide accurate and up-to-date details, but there is a chance that they can be mistaken. That is the reason we are not accountable for any misinformation presented on this website and for the stance that people interpret that information, whether correct or incorrect.</p><p>The data present on this website is widely checked. However, our team is not responsible for any loss, inconvenience, or judgment of those details by an individual. The user must utilize the website at their own will and risk.</p>',
        ]);

        $this->settings([
            'general.home_page_id' => $home->id,
            'general.blog_page_id' => Page::where('path', 'blog')->value('id'),
            'general.hotels_page_id' => $hotels->id,
            'integrations.arrivals_page_id' => $arrivals->id,
            'integrations.departures_page_id' => $departures->id,
        ]);
    }

    private function seedMenus(): void
    {
        $this->menu('header', [
            ['label' => 'Flights', 'url' => '#', 'children' => [
                ['label' => 'Arrivals', 'page' => 'flight-arrivals', 'icon' => 'plane-arrival'],
                ['label' => 'Departures', 'page' => 'flight-departures', 'icon' => 'plane-departure'],
                ['label' => 'Flight Status', 'page' => 'flights/delays-and-cancellations', 'icon' => 'clock'],
            ]],
            ['label' => 'Terminals', 'url' => '#', 'children' => [
                ['label' => 'North Terminal', 'page' => 'north-terminal'],
                ['label' => 'Central Terminal', 'page' => 'central-terminal'],
                ['label' => 'South Terminal', 'page' => 'south-terminal'],
            ]],
            ['label' => 'Transportation', 'page' => 'transportation'],
            ['label' => 'Car Rental', 'url' => '#', 'children' => [
                ['label' => 'SIXT', 'page' => 'car-rental/sixt-rent-a-car'],
                ['label' => 'Budget', 'page' => 'car-rental/budget-rent-a-car'],
                ['label' => 'Alamo', 'page' => 'car-rental/alamo-rent-a-car'],
                ['label' => 'Hertz', 'page' => 'car-rental/hertz-rent-a-car'],
                ['label' => 'Avis', 'page' => 'car-rental/avis-rent-a-car'],
                ['label' => 'National', 'page' => 'car-rental/national-rent-a-car'],
                ['label' => 'All Car Rental', 'page' => 'car-rental'],
            ]],
            ['label' => 'Passenger Info', 'url' => '#', 'children' => [
                ['label' => 'Parking', 'page' => 'parking', 'icon' => 'parking'],
                ['label' => 'Lost & Found', 'page' => 'lost-and-found', 'icon' => 'luggage'],
                ['label' => 'Wait Time', 'page' => 'wait-time', 'icon' => 'shield'],
                ['label' => 'Baggage Claim', 'page' => 'baggage-claim', 'icon' => 'luggage'],
                ['label' => 'Lounges', 'page' => 'lounges', 'icon' => 'star'],
                ['label' => 'Wi-Fi', 'page' => 'wi-fi', 'icon' => 'wifi'],
                ['label' => 'Shops', 'page' => 'shops-and-duty-free-stores', 'icon' => 'shopping'],
                ['label' => 'Restaurants & Food', 'page' => 'restaurants-and-food', 'icon' => 'utensils'],
                ['label' => 'Currency Exchange', 'page' => 'currency-exchange', 'icon' => 'globe'],
                ['label' => 'Hotels', 'page' => 'hotels', 'icon' => 'hotel'],
                ['label' => 'Map', 'page' => 'map', 'icon' => 'map'],
            ]],
            ['label' => 'Airlines', 'url' => '#', 'children' => [
                ['label' => 'American Airlines', 'page' => 'airlines/american-airlines'],
                ['label' => 'Delta Airlines', 'page' => 'airlines/delta-airlines'],
                ['label' => 'United Airlines', 'page' => 'airlines/united-airlines'],
                ['label' => 'Frontier Airlines', 'page' => 'airlines/frontier-airlines'],
                ['label' => 'Qatar Airways', 'page' => 'airlines/qatar-airways'],
                ['label' => 'Copa Airlines', 'page' => 'airlines/copa-airlines'],
                ['label' => 'Turkish Airlines', 'page' => 'airlines/turkish-airlines'],
                ['label' => 'LATAM Airlines', 'page' => 'airlines/latam-airlines'],
                ['label' => 'All Airlines', 'page' => 'airlines'],
            ]],
            ['label' => 'Blog', 'page' => 'blog'],
        ]);

        $this->menu('footer', [
            ['label' => 'Handy links', 'url' => '#', 'children' => [
                ['label' => 'Arrivals', 'page' => 'flight-arrivals'],
                ['label' => 'Departures', 'page' => 'flight-departures'],
                ['label' => 'Map', 'page' => 'map'],
                ['label' => 'North Terminal', 'page' => 'north-terminal'],
                ['label' => 'Central Terminal', 'page' => 'central-terminal'],
                ['label' => 'South Terminal', 'page' => 'south-terminal'],
                ['label' => 'Baggage Claim', 'page' => 'baggage-claim'],
            ]],
            ['label' => 'Airport guide', 'url' => '#', 'children' => [
                ['label' => 'Car Rental', 'page' => 'car-rental'],
                ['label' => 'Parking', 'page' => 'parking'],
                ['label' => 'Restaurants & Food', 'page' => 'restaurants-and-food'],
                ['label' => 'Lounges', 'page' => 'lounges'],
                ['label' => 'Hotels', 'page' => 'hotels'],
                ['label' => 'Shops & Duty-Free', 'page' => 'shops-and-duty-free-stores'],
                ['label' => 'Contact Us', 'page' => 'contact-us'],
            ]],
            ['label' => 'Transportation', 'url' => '#', 'children' => [
                ['label' => 'All transportation', 'page' => 'transportation'],
                ['label' => 'Airport to Downtown Miami', 'page' => 'transportation/airport-to-downtown-miami'],
                ['label' => 'Airport to Miami Beach', 'page' => 'transportation/airport-to-miami-beach'],
                ['label' => 'Airport to South Beach', 'page' => 'transportation/airport-to-south-beach'],
                ['label' => 'Airport to Cruise Port', 'page' => 'transportation/airport-to-miami-cruise-port'],
                ['label' => 'Airport to Key Largo', 'page' => 'transportation/airport-to-key-largo'],
            ]],
        ]);

        $this->menu('legal', [
            ['label' => 'Privacy Policy', 'page' => 'privacy-policy'],
            ['label' => 'Disclaimer', 'page' => 'disclaimer'],
            ['label' => 'Contact', 'page' => 'contact-us'],
            ['label' => 'Sitemap', 'url' => '/sitemap'],
        ]);

        $this->menu('quick_links', [
            ['label' => 'Arrivals', 'page' => 'flight-arrivals', 'icon' => 'plane-arrival'],
            ['label' => 'Departures', 'page' => 'flight-departures', 'icon' => 'plane-departure'],
            ['label' => 'Parking', 'page' => 'parking', 'icon' => 'parking'],
            ['label' => 'Wait Time', 'page' => 'wait-time', 'icon' => 'shield'],
            ['label' => 'Transportation', 'page' => 'transportation', 'icon' => 'taxi'],
            ['label' => 'Flight Status', 'page' => 'flights/delays-and-cancellations', 'icon' => 'clock'],
        ]);

        $this->menu('featured', [
            ['label' => 'Wheelchair Requests', 'page' => 'wheelchair-assistance', 'image' => 'wheelchair.webp', 'description' => 'Request mobility assistance from check-in to the gate.'],
            ['label' => 'Wi-Fi at MIA', 'page' => 'wi-fi', 'image' => 'wifi.webp', 'description' => 'Free, unlimited Wi-Fi across every concourse.'],
            ['label' => 'Baggage Claim', 'page' => 'baggage-claim', 'image' => 'baggage-claim.webp', 'description' => 'Find your carousel and what to do if a bag is missing.'],
            ['label' => 'Lost and Found', 'page' => 'lost-and-found', 'image' => 'lost-and-found.webp', 'description' => 'How to report and recover lost items.'],
            ['label' => 'Transportation', 'page' => 'transportation', 'image' => 'transportation.webp', 'description' => 'Trains, buses, taxis, rideshare and rental cars.'],
            ['label' => 'Parking Options', 'page' => 'parking', 'image' => 'parking.webp', 'description' => 'Garages, economy lots and the cell phone waiting area.'],
            ['label' => 'Terminal Map', 'page' => 'map', 'image' => 'map.webp', 'description' => 'Gates, concourses, lounges and amenities.'],
            ['label' => 'Travelling with Children', 'page' => 'blog', 'image' => 'travel-with-children.webp', 'description' => 'Family-friendly tips for a smoother trip.'],
        ]);
    }

    private function transportationContent(): string
    {
        $modes = [
            ['Metrorail', 'metrorail.webp', 'It is one of the convenient ways to travel to and from the airport. Take the free MIA Mover from the airport to the Miami Intermodal Center, where passengers can board the Metrorail Orange Line. This line connects directly to popular areas like Downtown Miami and Brickell.', null],
            ['Tri-Rail', 'tri-rail.webp', 'A commuter train that connects Miami International Airport to Broward and Palm Beach counties, including cities like Fort Lauderdale and West Palm Beach.', null],
            ['Metrobus', 'metrobus.webp', 'Several Metrobus routes serve MIA, providing connections to multiple parts of Miami-Dade County. It is a budget-friendly option for exploring the city.', null],
            ['Airport shuttle services', 'shuttle.webp', 'Shuttle services offer convenient door-to-door rides to hotels and destinations across Miami. Many hotels provide free shuttle options for guests.', null],
            ['Taxi services', 'taxi.webp', 'Fast and convenient taxis are available at arrivals with metered fares and flat-rate options for popular routes.', null],
            ['Ridesharing apps', 'rideshare.webp', 'Uber and Lyft operate at MIA with dedicated pickup zones and easy availability.', null],
            ['Car rental', 'car-rental.webp', 'The MIA Rental Car Center hosts all major car rental companies and is accessible via the MIA Mover.', '/car-rental'],
            ['Brightline', 'brightline.webp', 'A fast intercity train service linked to MIA via scheduled shuttle buses.', null],
        ];

        $cards = collect($modes)->map(function ($mode) {
            [$title, $file, $text, $url] = $mode;
            $image = $this->image($file, $title);
            $button = $url ? ' button="Learn more" url="'.$url.'"' : ' button=""';

            return "[card title=\"{$title}\" image=\"{$image->path}\"{$button}]<p>{$text}</p>[/card]";
        })->implode("\n");

        return <<<HTML
<h2>Miami International Airport ground transportation</h2>
<p>Miami International Airport (MIA) serves millions of business travellers and tourists heading to South Florida, Latin America, and the Caribbean. Therefore, the airport offers easy connections from and into the airport and the surrounding areas. Miami airport transportation involves a range of user-friendly modes of transport designed to suit all kinds of budgets and travel needs.</p>
<p>From public transport systems, including the MIA Mover, Metrorail, and Metrobus, through regional links, ranging from Tri-Rail and intercity buses, tourists are in a position to access downtown Miami, its neighbouring areas, and as far as Fort Lauderdale or West Palm Beach. Those requiring more direct and personal transport can use taxis, rideshare services like Uber and Lyft, and black car services at the curbside. Many of the Miami hotels also have free shuttle transport, making it convenient for passengers to access the airport directly.</p>
<p>Additionally, car rental centers in the Miami airport ground transportation services allow visitors to travel through South Florida at their own pace. Accordingly, be it business, leisure, or connecting via cruise, MIA’s ground transport infrastructure allows access, comfort, and efficiency within and through the airport.</p>
<h2>Transport services provided by MIA</h2>
{$cards}
[child_pages title="Popular routes from MIA"]
HTML;
    }

    private function privacyContent(): string
    {
        return <<<'HTML'
<p>Welcome to miamiairportmia-info.com (“we,” “us,” or “our”). This Privacy Policy explains how we collect, use, and protect your personal information when you visit our website.</p>
<h2>Information we collect</h2>
<h3>1. Personal information you provide</h3>
<p>We may collect personal details such as your name, email address, or any other information you voluntarily submit when:</p>
<ul><li>Contacting us via forms or email</li><li>Subscribing to updates</li><li>Requesting information about Miami International Airport</li></ul>
<p>We do not collect sensitive personal data. Please ensure all information you provide is accurate and up to date.</p>
<h3>2. Automatically collected data</h3>
<p>When you browse our site, we automatically gather non-identifiable data such as:</p>
<ul><li>IP address and device type</li><li>Browser settings and operating system</li><li>Pages visited and interaction history</li><li>Language preferences and approximate location</li></ul>
<p>This helps us improve site performance, security, and user experience.</p>
<h2>Cookies &amp; tracking technologies</h2>
<p>We use cookies and similar technologies to analyze website traffic, enhance user experience and remember your preferences. You can manage cookie settings through your browser.</p>
<h2>How we use your information</h2>
<ul><li>Respond to inquiries and provide requested information</li><li>Improve website functionality and content</li><li>Ensure security and prevent fraud</li><li>Comply with legal obligations</li></ul>
<p>We may also use your data for additional purposes with your explicit consent.</p>
<h2>Data retention</h2>
<p>We retain your personal information only as long as necessary to fulfill the purposes outlined in this policy. Once no longer needed, your data will be securely deleted or anonymized.</p>
<h2>Your privacy rights</h2>
<p>Depending on your location, you may have the right to access the personal data we hold about you, request corrections or updates, delete your personal data, and withdraw consent for data processing. To exercise these rights, contact us at <a href="mailto:info@miamiairportmia-info.com">info@miamiairportmia-info.com</a>.</p>
<h2>Policy updates</h2>
<p>We may update this Privacy Policy periodically. Significant changes will be communicated via a prominent notice on our website. We encourage you to review this page regularly.</p>
<h2>Contact us</h2>
<p>If you have questions or concerns about this Privacy Policy or how we handle your data, please email <a href="mailto:info@miamiairportmia-info.com">info@miamiairportmia-info.com</a>.</p>
HTML;
    }
}
