import { request } from '../core/http';

/**
 * Instant search suggestions for <form data-live-search> containing an input[name=q]
 * and a [data-live-results] list.
 */
export function initLiveSearch() {
    document.querySelectorAll('[data-live-search]').forEach((form) => {
        const input = form.querySelector('input[name="q"]');
        const results = form.querySelector('[data-live-results]');
        if (!input || !results) return;

        let timer;
        let controller;

        const close = () => {
            results.hidden = true;
            input.setAttribute('aria-expanded', 'false');
        };

        const render = (items, term) => {
            results.innerHTML = '';
            if (!items.length) {
                results.innerHTML = `<p class="px-4 py-6 text-center text-sm text-fg-muted">No results for “<span></span>”.</p>`;
                results.querySelector('span').textContent = term;
            }
            items.forEach((item) => {
                const a = document.createElement('a');
                a.href = item.url;
                a.setAttribute('role', 'option');
                a.className = 'block rounded-lg px-4 py-3 hover:bg-surface-muted focus:bg-surface-muted focus:outline-none';
                a.innerHTML = '<span class="flex items-center justify-between gap-3"><span class="font-medium text-fg"></span><span class="badge badge-neutral"></span></span><span class="mt-0.5 block line-clamp-1 text-xs text-fg-muted"></span>';
                a.querySelector('.font-medium').textContent = item.title;
                a.querySelector('.badge').textContent = item.type;
                a.querySelector('.text-xs').textContent = item.excerpt;
                results.appendChild(a);
            });
            const all = document.createElement('a');
            all.href = `${form.action}?q=${encodeURIComponent(term)}`;
            all.className = 'mt-1 block border-t border-line px-4 py-3 text-sm font-semibold text-primary hover:bg-surface-muted';
            all.textContent = 'See all results →';
            results.appendChild(all);
            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const term = input.value.trim();
            if (term.length < 2) return close();

            timer = setTimeout(async () => {
                controller?.abort();
                controller = new AbortController();
                results.hidden = false;
                results.innerHTML = '<div class="space-y-2 p-4"><div class="skeleton h-4 w-3/4"></div><div class="skeleton h-4 w-1/2"></div></div>';
                try {
                    const response = await request(`${form.action}?q=${encodeURIComponent(term)}`, { signal: controller.signal });
                    render(response.data.results, term);
                } catch (error) {
                    if (error.name !== 'AbortError') close();
                }
            }, 250);
        });

        document.addEventListener('click', (e) => !form.contains(e.target) && close());
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                results.querySelector('a')?.focus();
            }
        });
        results.addEventListener('keydown', (e) => {
            const links = [...results.querySelectorAll('a')];
            const index = links.indexOf(document.activeElement);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                links[Math.min(index + 1, links.length - 1)]?.focus();
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                (index <= 0 ? input : links[index - 1]).focus();
            }
            if (e.key === 'Escape') {
                close();
                input.focus();
            }
        });
    });
}
