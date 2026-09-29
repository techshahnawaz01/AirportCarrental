/**
 * Directory template: instant filtering of A–Z listings.
 */
export function initDirectories() {
    document.querySelectorAll('[data-directory]').forEach((directory) => {
        const input = directory.querySelector('[data-directory-search]');
        const count = directory.querySelector('[data-directory-count]');
        const empty = directory.querySelector('[data-directory-empty]');
        if (!input) return;

        const items = [...directory.querySelectorAll('[data-directory-item]')];
        const groups = [...directory.querySelectorAll('[data-directory-group]')];

        input.addEventListener('input', () => {
            const terms = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
            let shown = 0;

            items.forEach((item) => {
                const match = terms.every((term) => item.dataset.search.includes(term));
                item.hidden = !match;
                shown += match ? 1 : 0;
            });

            groups.forEach((group) => {
                const visible = group.querySelector('[data-directory-item]:not([hidden])');
                group.hidden = !visible;
                directory.querySelector(`[data-directory-letter="${CSS.escape(group.dataset.directoryGroup)}"]`)?.classList.toggle('opacity-30', !visible);
            });

            count.textContent = shown;
            empty.hidden = shown > 0;
        });
    });
}
