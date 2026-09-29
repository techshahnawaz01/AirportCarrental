import { request } from '../core/http';
import { toast } from '../core/toast';

/**
 * Filter / search / paginate listings without full page reloads.
 * <div data-ajax-table data-url="/admin/pages"> contains a [data-table-filters] form
 * and a [data-table-body] container that the server re-renders.
 */
export function initAjaxTables() {
    document.querySelectorAll('[data-ajax-table]').forEach((wrapper) => {
        const form = wrapper.querySelector('[data-table-filters]');
        const target = wrapper.querySelector('[data-table-body]');
        let controller;
        let timer;

        const load = async (url, push = true) => {
            controller?.abort();
            controller = new AbortController();
            target.classList.add('opacity-50', 'pointer-events-none');
            target.setAttribute('aria-busy', 'true');
            try {
                const partialUrl = new URL(url, window.location.origin);
                partialUrl.searchParams.set('partial', '1');
                const response = await request(partialUrl, { signal: controller.signal });
                target.innerHTML = response.data.html;
                if (push) {
                    partialUrl.searchParams.delete('partial');
                    history.replaceState(null, '', partialUrl);
                }
            } catch (error) {
                if (error.name !== 'AbortError') toast.error(error.message);
            } finally {
                target.classList.remove('opacity-50', 'pointer-events-none');
                target.removeAttribute('aria-busy');
            }
        };

        const fromForm = () => {
            const url = new URL(wrapper.dataset.url, window.location.origin);
            new FormData(form).forEach((value, key) => value !== '' && url.searchParams.set(key, value));
            return url;
        };

        form?.addEventListener('submit', (e) => {
            e.preventDefault();
            load(fromForm());
        });
        form?.addEventListener('change', (e) => e.target.matches('select, input[type=checkbox], input[type=radio]') && load(fromForm()));
        form?.querySelectorAll('input[type="search"]').forEach((input) =>
            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => load(fromForm()), 350);
            }),
        );

        wrapper.addEventListener('click', (e) => {
            const link = e.target.closest('[data-table-body] nav[aria-label="Pagination"] a, [data-table-link]');
            if (!link) return;
            e.preventDefault();
            load(link.href);
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        wrapper.addEventListener('refresh', () => load(window.location.href, false));
        wrapper.addEventListener('rows:changed', () => {
            if (!target.querySelector('tbody tr, [data-row]')) load(window.location.href, false);
        });
    });
}
