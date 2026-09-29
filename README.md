# Site CMS — reusable Laravel website foundation

A modern, admin-driven website platform built with **Laravel 12, Blade, Tailwind CSS 4, vanilla JS (Fetch API) and Vite**.
It currently powers the **Miami International Airport guide** (miamiairportmia-info.com), rebuilt from the original Core PHP site,
but nothing in the application code is Miami-specific: branding, colours, contact details, pages, navigation, SEO and
images all live in the database and are managed from the admin panel. A new website is a new seeder (or a few minutes in the admin), not a rewrite.

---

## Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Environment](#environment)
5. [Database, migrations & seeding](#database-migrations--seeding)
6. [Admin login](#admin-login)
7. [Storage](#storage)
8. [Build commands](#build-commands)
9. [Importing content from an existing site](#importing-content-from-an-existing-site)
10. [Flight data & wait-time integrations](#flight-data--wait-time-integrations)
11. [Production deployment](#production-deployment)
12. [Configuration reference](#configuration-reference)
13. [Managing the site](#managing-the-site) — pages, branding, colours, menus, SEO
14. [Content blocks (shortcodes)](#content-blocks-shortcodes)
15. [Creating another website from this foundation](#creating-another-website-from-this-foundation)
16. [Architecture](#architecture)
17. [Testing](#testing)
18. [Migrating from the legacy Core PHP site](#migrating-from-the-legacy-core-php-site)

---

## Features

**Front end**
- Mobile-first Tailwind UI: sticky header with dropdowns, mobile drawer, live search suggestions, footer with newsletter sign-up.
- Page templates (chosen per page in the admin):

  | Template | Best for |
  | --- | --- |
  | Standard | Most content pages — content with a help/related sidebar |
  | Full width | Tools and wide content (flight boards, legal pages) |
  | Guide | Long articles — sticky table of contents built from H2/H3 headings, reading progress bar, read time |
  | Landing | Campaign/service pages — centred hero with eyebrow and two configurable CTA buttons, block-based sections |
  | Home | The home page — hero with live search |
  | Listing | Section index pages — paginated card grid of child pages |
  | Directory | Long lists (airlines, car rental) — A–Z groups with instant search and letter jump links |
  | Magazine | Blog index — large featured latest story, article grid, search |
  | Blog post | Articles with date, read time, FAQs and comments |
  | Hotel detail | Gallery, location & map, rooms with amenities |
  | Contact | Contact details from Settings, enquiry form, map |
- Hierarchical URLs (`/transportation/airport-to-miami-beach`), drafts with staff-only preview, scheduled publishing.
- FAQ accordions with FAQPage structured data, moderated comments with star ratings, AJAX contact form with honeypot and rate limiting.
- Branded error pages (403, 404, 419, 422, 429, 500, 503).
- SEO: per-page meta title/description/keywords, canonical URL, Open Graph, Twitter cards, robots meta, JSON-LD
  (WebSite, Article, Hotel, BreadcrumbList, FAQPage), dynamic `sitemap.xml` for search engines, a designed HTML sitemap at
  `/sitemap` (pages grouped by section with instant search), `robots.txt`, 301/302 redirect manager.
- Theme colours are CSS variables driven by admin settings — change the primary colour and the whole site follows.

**Admin panel** (`/admin`)
- Dashboard with real counts, recent enquiries, content overview, recent activity and a website health panel.
- Pages / Blog posts / Hotels (configurable content types) with rich-text editor (TinyMCE, self-hosted), FAQ repeater,
  hotel gallery & rooms, featured/OG images, SEO panel, trash & restore.
- Media library: drag & drop multi-upload, de-duplication, alt text, picker modal used everywhere images are chosen.
- Navigation builder for every menu location (header, footer columns, legal links, home tiles, featured cards).
- Enquiries inbox (read/unread), comment moderation, newsletter subscribers with CSV export.
- Settings: branding (logo, footer logo, admin logo, favicon), theme colours, general, contact, social, integrations.
- SEO screen: defaults, Google Analytics, Search Console / Bing verification, indexing switch, robots.txt rules, redirects.
- Users with roles (**Administrator** — everything; **Editor** — content, media, comments, enquiries), activity log, profile & password.
- AJAX everywhere it helps: forms, filters, search, pagination, toggles, deletes — with toasts, confirmation dialogs,
  loading states, empty states and field-level validation errors. Light/dark mode.

All AJAX endpoints return one envelope:

```json
{ "success": true,  "message": "Settings updated successfully.", "data": {} }
{ "success": false, "message": "Please fix the errors.", "errors": { "field": ["…"] } }
```

with the proper HTTP status (200/201, 401, 403, 404, 419, 422, 429, 500).

---

## Requirements

- PHP **8.2+** with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `dom`, `gd` (GD is optional; used to downscale huge uploads)
- MySQL 8+ or MariaDB 10.4+
- Composer 2
- Node.js 20+ and npm (only needed to build assets; production servers can receive the prebuilt `public/build`)

---

## Installation

```bash
git clone https://github.com/techshahnawaz01/AirportCarrental.git
cd AirportCarrental

composer install
npm install

cp .env.example .env
php artisan key:generate
# edit .env → database credentials, ADMIN_EMAIL / ADMIN_PASSWORD

php artisan migrate:fresh --seed
php artisan storage:link
npm run build

php artisan serve          # http://127.0.0.1:8000
```

For local development with hot reloading run `npm run dev` in a second terminal.

**XAMPP (macOS/Windows):** create a database (e.g. `AirportCarrental`) in phpMyAdmin, set `DB_USERNAME=root` and an empty
`DB_PASSWORD`, then run the commands above.

---

## Environment

Everything environment-specific lives in `.env` (never commit it). The important keys:

| Key | Purpose |
| --- | --- |
| `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG` | Standard Laravel settings. Use `APP_ENV=production`, `APP_DEBUG=false` live. |
| `DB_*` | Database connection. |
| `MAIL_*` | SMTP for password resets and new-enquiry notifications. |
| `FILESYSTEM_DISK=public` | Uploads go to `storage/app/public`. |
| `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | First administrator created by the seeder. If the password is empty a random one is generated and printed. |
| `CMS_SITE_SEEDER` | Which site content seeder runs on `--seed` (default: the Miami seeder). |
| `AVIATIONSTACK_KEYS` | Comma separated Aviationstack API keys (rotated automatically when one hits its monthly limit). |
| `TSA_WAIT_TIMES_KEY` | tsawaittimes.com API key for the security wait-time widget. |

Site content (name, logo, colours, phone numbers, social links, analytics IDs…) is **not** in `.env` — it's managed in the admin.

---

## Database, migrations & seeding

```bash
php artisan migrate               # create/upgrade tables
php artisan migrate:fresh --seed  # rebuild from scratch with default content
```

Tables: `users`, `settings`, `media`, `pages`, `faqs`, `comments`, `menus`, `menu_items`, `enquiries`, `subscribers`,
`redirects`, `activity_logs`, `flight_snapshots`, plus Laravel's `sessions`, `cache`, `jobs`, `password_reset_tokens`.
Foreign keys, unique constraints (e.g. `pages.path`, `media.path`, `settings.key`), indexes on every filter column
and soft deletes (pages, comments, enquiries, users) are defined in the migrations.

Seeding runs:
1. `Database\Seeders\CoreSeeder` — first admin, default settings from `config/settings.php`, empty menus.
2. The site seeder from `CMS_SITE_SEEDER` — for this project `Database\Seeders\Sites\MiamiAirportSeeder`
   (branding, theme, contact details, SEO defaults, core pages, FAQs, navigation, legacy redirect).

---

## Admin login

Open **`/admin`** and sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`
(local default in `.env.example`: `admin@example.com`). Change the password straight away under **Profile**.

Forgotten passwords: **Forgot password?** on the login screen sends a reset link (configure `MAIL_*`).
Login is rate-limited (5 attempts per email/IP per minute), sessions are regenerated on login, deactivated accounts are signed out immediately.

---

## Storage

Uploads are stored on the `public` disk:

```
storage/app/public/media/branding/…     logos & favicon
storage/app/public/media/YYYY/MM/…      media library uploads
```

Run `php artisan storage:link` once per environment so `/storage/...` URLs work.
Identical files are detected by SHA-1 and never stored twice. Only JPG, PNG, WebP and GIF (and ICO for the favicon) are
accepted, validated by MIME type, size and — for the favicon — square dimensions. SVG uploads are intentionally not allowed.

**Shared hosting without symlinks:** copy/sync `storage/app/public` to `public/storage`, or ask your host to enable `symlink()`.

---

## Build commands

```bash
npm run dev     # Vite dev server with hot reload
npm run build   # production assets → public/build
```

The public bundle is ~11 KB JS + CSS. The rich-text editor (TinyMCE) is split into its own chunk and only loaded on admin edit screens.

---

## Importing content from an existing site

Any site with an XML sitemap can be migrated into the CMS:

```bash
php artisan cms:import-sitemap https://miamiairportmia-info.com/sitemap.xml
```

For each URL it imports the title, meta title/description/keywords, robots flags, featured/hero image, main content
(images are downloaded into the media library, same-site links are rewritten to clean relative paths), FAQs, and for
hotels the gallery, rooms and location. URL hierarchy is preserved. Known legacy widgets are converted into content blocks.

Options: `--only=blog` (path prefix), `--limit=10`, `--update` (overwrite existing pages — careful, this also overwrites pages
you edited or seeded), `--without-images`, `--selector=.content-area,.entry-content,#reach,article,main`.

Import a folder of images into the media library: `php artisan cms:import-media legacy/Pic`.

---

## Flight data & wait-time integrations

These are optional and switch themselves off when not configured.

1. Put your Aviationstack keys in `AVIATIONSTACK_KEYS` and the TSA key in `TSA_WAIT_TIMES_KEY`.
2. In **Settings → Integrations** set the airport IATA code (e.g. `MIA`) and pick the arrivals/departures pages.
3. Schedule Laravel's scheduler (see below). `flights:sync` runs hourly and stores snapshots; delays/cancellations are cached for 6 hours to protect the API quota.

Run it manually with `php artisan flights:sync`.

---

## Production deployment

Works on any PHP 8.2+ host (Hostinger, cPanel shared hosting, VPS).

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # or upload public/build from your machine
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

- Point the domain's document root at the **`public/`** directory. On shared hosting where that's impossible, move the
  contents of `public/` into `public_html/` and update the two paths in `public_html/index.php`.
- `.env`: `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL` (https), real `MAIL_*`.
- Cron (scheduler, needed for flight data):
  `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
- Enforce HTTPS and your canonical host (www / non-www) at the web server or with the host's settings.
- After changing settings directly in the database, clear the cache: **Dashboard → Clear cache** or `php artisan cache:clear`.

---

## Configuration reference

| File | What it controls |
| --- | --- |
| `config/settings.php` | Schema of every admin setting: groups, field types, validation rules, generic defaults. Add a field here and it appears in the admin automatically. |
| `config/cms.php` | Content types, page templates, menu locations, icons, pagination, upload limits, registered shortcodes, integration keys, site seeder. |
| `.env` | Credentials and environment. |

Read a setting anywhere with `settings('contact.phone')`; image settings with `settings()->url('branding.logo')`.
Settings are cached and the cache is flushed automatically on every change.

---

## Managing the site

### Create a page
**Admin → Pages → New page.** Enter a title (the slug is generated), write content, choose a **template** and optionally a
**parent** (which builds the URL), add FAQs, set SEO fields and a featured image, then **Create**.
Blog posts and hotels work the same way from their own menu items and default to the right parent and template.
Unpublished pages are only visible to signed-in staff (with a preview banner).

### Change branding
**Settings → Branding:** site name, tagline, logo, footer logo, admin logo and favicon. Upload, pick from the library, replace or remove — previews update instantly.

### Change colours
**Settings → Theme:** primary, secondary, accent, button, text and background colours. They're printed as CSS variables
(`--brand-primary`, …) and every Tailwind utility such as `bg-primary`, `text-secondary`, `bg-button` reads them — no rebuild needed.

### Navigation
**Navigation** lists every menu location. Items can link to a page (URL stays correct if the slug changes) or a custom URL,
have one level of children (dropdowns / footer columns), an icon (home tiles) and an image + description (featured cards). Reorder with the arrows.

### SEO
**SEO** sets default meta, the title suffix, OG image, Google Analytics ID, Search Console/Bing verification, whether search engines may index the site,
extra robots.txt rules, and redirects. Per-page SEO lives in each page's SEO panel.

---

## Content blocks (shortcodes)

Editors can place these anywhere in page content:

| Shortcode | Output |
| --- | --- |
| `[contact_form title="…" subject="…"]` | AJAX contact form (enquiries appear in the admin). |
| `[faqs]` / `[faqs page="path"]` | FAQ accordion of this or another page. |
| `[child_pages parent="path" limit="12" title="…"]` | Card grid of child pages. |
| `[latest_posts parent="blog" limit="5" title="…"]` | Featured + list of latest posts. |
| `[link_cards menu="featured" style="images\|icons" title="…"]` | Cards/tiles from a navigation menu. |
| `[cta title="…" text="…" button="…" url="…" image="media/path"]` | Banner call to action. |
| `[call_cta title="…" text="…"]` | Call button using the phone number from Settings → Contact. |
| `[media_text image="media/path" title="…" position="left\|right"]…[/media_text]` | Image beside rich text. |
| `[card title="…" image="media/path" button="…" url="…"]…[/card]` | Horizontal card. |
| `[flight_widget]`, `[flight_board type="arrivals\|departures"]`, `[flight_disruptions]`, `[wait_times]` | Airport integrations (hide/degrade gracefully when not configured). |

Image paths are shown in the media library's details dialog. Add your own block by implementing
`App\Shortcodes\Shortcode` and registering it in `config/cms.php`.

---

## Creating another website from this foundation

1. Clone the repository into a new project and create a new database.
2. Copy `database/seeders/Sites/MiamiAirportSeeder.php` to e.g. `AcmeSeeder.php`, put its images in a new
   `database/seeders/Sites/acme-assets/` folder and change the settings, pages and menus. Everything goes through the small
   `SiteSeeder` helper API (`settings()`, `image()`, `page()`, `menu()`).
3. Set `CMS_SITE_SEEDER="Database\Seeders\Sites\AcmeSeeder"` in `.env` and run `php artisan migrate:fresh --seed`.
4. Or skip step 2 entirely: seed with an empty site seeder and configure everything in the admin
   (Settings → Branding/Theme/Contact/Social, Pages, Navigation, SEO).
5. Need different content types, templates or menu locations? Edit `config/cms.php` and add a Blade template in
   `resources/views/frontend/templates/` (the key in `cms.templates` is the view name). Template-specific admin fields
   go in a `resources/views/admin/pages/_<name>-fields.blade.php` partial wrapped in `data-template-section="<name>"`. Optional integrations (flights, TSA) stay dormant if their keys are empty.

No controller, service or component needs to change.

---

## Architecture

```
app/
  Console/Commands/     flights:sync, cms:import-sitemap, cms:import-media
  Http/Controllers/     Frontend/* (public site) and Admin/* (panel), thin controllers
  Http/Middleware/      SecurityHeaders, EnsureUserIsActive, ApplySiteLocale
  Http/Requests/        Form Requests (pages, users, menu items, redirects, enquiries, comments)
  Models/               Eloquent models
  Notifications/        NewEnquiry mail
  Services/             Settings, Media, Page, Menu, Seo, ShortcodeRenderer, HtmlSanitizer, ActivityLogger, FlightData, WaitTimes
  Shortcodes/           Content blocks
config/cms.php, config/settings.php
resources/
  css/app.css           Tailwind 4 theme tokens (brand + light/dark surfaces) and component classes
  js/core/              http, toast, dialog, forms, actions — shared by site & admin
  js/frontend/          navigation, live search, tabs, flights, gallery
  js/admin/             layout, ajax tables, media picker, image/gallery fields, repeaters, editor, menu builder
  views/components/     ui/* (fields, buttons, cards, modals, alerts…), site/* (header, footer, hero…), admin/*
  views/frontend/       templates, shortcode views, search, sitemap
  views/admin/          admin screens
routes/web.php          public system routes + admin group (prefix, name prefix, auth/active/can middleware)
routes/cms.php          catch-all page route (registered last)
database/seeders/       CoreSeeder + Sites/* site seeders
```

Security: CSRF on every form and AJAX call, Form Request validation, mass-assignment protection, Eloquent/bindings only,
Blade escaping for all user input, server-side HTML sanitising of editor content (scripts, event handlers, `javascript:` URLs,
untrusted iframes removed), strict upload validation, role gates (`access-admin`, `manage-site`), rate limits on login and public forms,
honeypots, security headers, `noindex` on admin pages.

Performance: settings and menus cached, eager loading, indexed filters, pagination everywhere, lazy-loaded images with dimensions,
large uploads downscaled, editor code-split.

---

## Testing

```bash
php artisan test
```

Feature tests cover the seeded site, public pages, forms, SEO endpoints, authentication, authorisation, page CRUD & URL hierarchy,
settings, uploads and the media library; unit tests cover the sanitizer and shortcode parser. Tests use in-memory SQLite and a fake disk.

---

## Migrating from the legacy Core PHP site

The original site was a custom Core PHP router with a `pages` table, theme templates, a separate Bootstrap admin and
hard-coded API keys. It has been rebuilt as follows:

| Legacy | Now |
| --- | --- |
| `index.php` router + `config/routes.php` | `routes/web.php`, `routes/cms.php`, `Frontend\PageController` |
| `theme/*.php` templates | Blade templates + components |
| `pages` / `page_meta` / `blog_faq` / `recycle_bin` | `pages` (with `data` JSON + soft deletes), `faqs` |
| `comment_section`, `contact_form`, `subscribers` | `comments`, `enquiries`, `subscribers` |
| `admin` table + custom sessions | `users` + Laravel auth, roles, password reset |
| `flight_data_arr/dep` + `update-arrivals-departures.php` (basic-auth cron URL) | `flight_snapshots` + scheduled `flights:sync` |
| Shortcodes (`[wait_times]`, `[flight_arr_dep]`, `[support_card]`, …) | Shortcode classes (`[wait_times]`, `[flight_board]`, `[call_cta]`, …) |
| API keys & DB passwords in PHP files | `.env` only |

The legacy code, images and a database backup are kept locally in `legacy/` (git-ignored — it contains credentials).
