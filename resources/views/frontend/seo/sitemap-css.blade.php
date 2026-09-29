@php
    $s = settings();
    $site = addcslashes((string) $s->get('branding.site_name'), '"\\');
    $primary = $s->get('theme.primary_color', '#0b5cab');
    $secondary = $s->get('theme.secondary_color', '#0f172a');
    $accent = $s->get('theme.accent_color', '#f59e0b');
@endphp
/* Styles sitemap.xml for people. Element names come from the sitemaps.org schema. */
@import url('https://fonts.bunny.net/css?family=inter:400,500,600,800&display=swap');

urlset {
    display: block;
    min-height: 100vh;
    margin: 0;
    padding: 0 0 48px;
    background: #f5f7fa;
    color: #1e293b;
    font: 14px/1.5 Inter, ui-sans-serif, system-ui, -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    counter-reset: page;
}

/* Header */
urlset::before {
    content: "XML Sitemap · {{ $site }}";
    display: block;
    margin: 0 0 28px;
    padding: 44px max(20px, calc((100% - 1088px) / 2)) 48px;
    background: {{ $secondary }} linear-gradient(120deg, {{ $primary }}, transparent 80%);
    border-bottom: 4px solid {{ $accent }};
    color: #fff;
    font-size: clamp(22px, 3.5vw, 34px);
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.2;
}

/* Each page */
url {
    counter-increment: page;
    display: grid;
    grid-template-columns: 44px 1fr auto;
    grid-template-areas: "num loc prio" "num date prio";
    column-gap: 16px;
    align-items: center;
    width: calc(100% - 32px);
    max-width: 1088px;
    margin: 0 auto;
    padding: 14px 20px;
    background: #fff;
    border: 1px solid #e3e8ef;
    border-top: 0;
    box-sizing: border-box;
}

url:first-of-type {
    border-top: 1px solid #e3e8ef;
    border-radius: 16px 16px 0 0;
}

url:last-of-type {
    border-radius: 0 0 16px 16px;
}

url:hover {
    background: #f8fafc;
}

url::before {
    content: counter(page);
    grid-area: num;
    color: #94a3b8;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
}

loc {
    grid-area: loc;
    display: block;
    color: #0f172a;
    font-weight: 600;
    word-break: break-all;
}

lastmod {
    grid-area: date;
    display: block;
    margin-top: 2px;
    color: #64748b;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
}

lastmod::before {
    content: "Last modified  ";
    color: {{ $primary }};
    font-weight: 600;
}

priority {
    grid-area: prio;
    display: block;
    padding: 3px 10px;
    border-radius: 999px;
    background: {{ $accent }};
    color: #0f172a;
    font-size: 12px;
    font-weight: 600;
}

priority::before {
    content: "Priority ";
    font-weight: 500;
}

/* Footer with the total */
urlset::after {
    content: counter(page) " URLs · Search engines like Google and Bing read this file to find every page and when it was last modified. It is generated automatically from published pages.";
    display: block;
    width: calc(100% - 32px);
    max-width: 720px;
    margin: 24px auto 0;
    line-height: 1.7;
    color: #64748b;
    font-size: 13px;
    text-align: center;
}

@media (max-width: 640px) {
    url {
        grid-template-columns: 1fr;
        grid-template-areas: "loc" "date" "prio";
        row-gap: 6px;
    }

    url::before {
        display: none;
    }

    priority {
        justify-self: start;
    }
}
