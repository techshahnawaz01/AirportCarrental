import { initImageFields } from './image-field';

/**
 * Repeatable field groups (FAQs, hotel rooms).
 * <div data-repeater="faqs"> [data-repeater-items] + <template data-repeater-template> + [data-repeater-add]
 * Use __INDEX__ in the template for the row index.
 */
export function initRepeaters(root = document) {
    root.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const items = repeater.querySelector('[data-repeater-items]');
        const template = repeater.querySelector('template[data-repeater-template]');
        const empty = repeater.querySelector('[data-repeater-empty]');
        let index = items.children.length;

        const refresh = () => {
            if (empty) empty.hidden = items.children.length > 0;
            [...items.children].forEach((row, i) => {
                const number = row.querySelector('[data-repeater-number]');
                if (number) number.textContent = i + 1;
            });
        };

        repeater.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('__INDEX__', `n${index++}`);
            items.insertAdjacentHTML('beforeend', html);
            const row = items.lastElementChild;
            initImageFields(row);
            row.querySelector('input, textarea')?.focus();
            refresh();
        });

        items.addEventListener('click', (e) => {
            const row = e.target.closest('[data-repeater-item]');
            if (!row) return;
            if (e.target.closest('[data-repeater-remove]')) {
                row.remove();
                refresh();
            }
            const move = e.target.closest('[data-repeater-move]');
            if (move) {
                const up = move.dataset.repeaterMove === 'up';
                const sibling = up ? row.previousElementSibling : row.nextElementSibling;
                if (sibling) up ? items.insertBefore(row, sibling) : items.insertBefore(sibling, row);
                refresh();
            }
        });

        refresh();
    });
}
