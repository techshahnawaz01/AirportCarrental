import { pickMedia } from './media-picker';

/**
 * Multi-image field storing media ids as name[] hidden inputs, re-orderable.
 */
export function initGalleryFields(root = document) {
    root.querySelectorAll('[data-gallery-field]').forEach((field) => {
        const list = field.querySelector('[data-gallery-list]');
        const name = field.dataset.name;

        const add = (item) => {
            if (list.querySelector(`input[value="${item.id}"]`)) return;
            const li = document.createElement('li');
            li.className = 'group relative aspect-square overflow-hidden rounded-lg border border-line bg-surface-muted';
            li.innerHTML = `<img class="size-full object-cover" alt=""><input type="hidden">
                <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-slate-950/60 p-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100">
                    <button type="button" data-move="-1" class="rounded px-1.5 text-white hover:bg-white/20" aria-label="Move left">←</button>
                    <button type="button" data-remove class="rounded px-1.5 text-white hover:bg-red-600" aria-label="Remove">✕</button>
                    <button type="button" data-move="1" class="rounded px-1.5 text-white hover:bg-white/20" aria-label="Move right">→</button>
                </div>`;
            li.querySelector('img').src = item.url;
            li.querySelector('img').alt = item.name || '';
            const input = li.querySelector('input');
            input.name = `${name}[]`;
            input.value = item.id;
            list.appendChild(li);
        };

        field.querySelector('[data-gallery-add]').addEventListener('click', async () => {
            const items = await pickMedia({ multiple: true });
            items?.forEach(add);
        });

        list.addEventListener('click', (e) => {
            const li = e.target.closest('li');
            if (e.target.closest('[data-remove]')) li.remove();
            const move = e.target.closest('[data-move]');
            if (move) {
                const sibling = move.dataset.move === '-1' ? li.previousElementSibling : li.nextElementSibling;
                if (sibling) move.dataset.move === '-1' ? list.insertBefore(li, sibling) : list.insertBefore(sibling, li);
            }
        });
    });
}
