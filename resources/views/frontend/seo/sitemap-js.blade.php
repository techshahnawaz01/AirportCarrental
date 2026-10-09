{{-- Loaded by sitemap.xml in browsers only; search engines read the plain XML. --}}
// Makes sitemap.xml easier to use in a browser: page addresses become links and
// dates read as "2026-09-30 4:01 AM" in the visitor's own time zone.
(() => {
    const pad = (n) => String(n).padStart(2, '0');
    const format = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${date.getHours() % 12 || 12}:${pad(date.getMinutes())} ${date.getHours() < 12 ? 'AM' : 'PM'}`;

    const run = () => {
        for (const loc of document.getElementsByTagName('loc')) {
            const url = loc.textContent.trim();
            if (loc.firstElementChild || !/^https?:\/\//.test(url)) continue;
            const link = document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
            link.href = url;
            link.textContent = url;
            loc.textContent = '';
            loc.appendChild(link);
        }

        for (const lastmod of document.getElementsByTagName('lastmod')) {
            const text = lastmod.textContent.trim();
            // A plain date has no time to show (or convert).
            if (/^\d{4}-\d{2}-\d{2}$/.test(text)) continue;
            const date = new Date(text);
            if (!Number.isNaN(date.getTime())) lastmod.textContent = format(date);
        }

        let zone = '';
        try {
            zone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch {}
        document.documentElement.setAttribute('data-note', `Dates are shown in your time zone${zone ? ` (${zone})` : ''}. `);
    };

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run) : run();
})();
