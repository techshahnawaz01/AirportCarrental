import { request } from '../core/http';
import { toast } from '../core/toast';
import { clearErrors } from '../core/forms';

/**
 * Menu editor: add/edit items in a shared modal and reorder with up/down buttons.
 */
export function initMenuBuilder() {
    const builder = document.querySelector('[data-menu-builder]');
    if (!builder) return;
    const dialog = document.getElementById('menu-item-dialog');
    const form = dialog.querySelector('form');

    const fill = (item = {}) => {
        clearErrors(form);
        form.reset();
        form.action = item.update_url || builder.dataset.storeUrl;
        form.querySelector('[name="_method"]').value = item.update_url ? 'PUT' : 'POST';
        dialog.querySelector('[data-dialog-title]').textContent = item.id ? 'Edit menu item' : 'Add menu item';
        ['label', 'url', 'page_id', 'parent_id', 'icon', 'description'].forEach((key) => {
            const field = form.elements[key];
            if (field) field.value = item[key] ?? '';
        });
        form.elements.open_in_new_tab.checked = !!item.open_in_new_tab;
        form.elements.is_active.checked = item.is_active ?? true;
        // An item cannot be its own parent.
        [...form.elements.parent_id.options].forEach((o) => (o.disabled = item.id && Number(o.value) === item.id));
        form.querySelector('[data-image-field]')?.dispatchEvent(new CustomEvent('image:saved', { detail: { url: item.image_url || '', value: item.image_id ?? '' } }));
        if (!item.image_url) {
            const field = form.querySelector('[data-image-field]');
            field?.querySelector('[data-image-remove]')?.setAttribute('hidden', '');
            field?.querySelector('[data-image-preview]')?.setAttribute('hidden', '');
            field?.querySelector('[data-image-empty]')?.removeAttribute('hidden');
        }
        dialog.showModal();
        form.elements.label.focus();
    };

    builder.addEventListener('click', async (e) => {
        const add = e.target.closest('[data-add-item]');
        if (add) return fill({ parent_id: add.dataset.parent || '' });

        const edit = e.target.closest('[data-edit-item]');
        if (edit) return fill(JSON.parse(edit.dataset.editItem));

        const move = e.target.closest('[data-move]');
        if (move) {
            const row = move.closest('[data-item-id]');
            const up = move.dataset.move === 'up';
            const sibling = up ? row.previousElementSibling : row.nextElementSibling;
            if (!sibling) return;
            up ? row.parentElement.insertBefore(row, sibling) : row.parentElement.insertBefore(sibling, row);
            await saveOrder();
        }
    });

    // Picking a page pre-fills an empty label.
    form.elements.page_id?.addEventListener('change', () => {
        const option = form.elements.page_id.selectedOptions[0];
        if (option?.value && !form.elements.label.value) form.elements.label.value = option.dataset.title;
    });

    async function saveOrder() {
        const items = [...builder.querySelectorAll('[data-item-id]')].map((row) => ({
            id: Number(row.dataset.itemId),
            parent_id: row.parentElement.closest('[data-item-id]')?.dataset.itemId || '',
        }));
        const body = new FormData();
        items.forEach((item, i) => {
            body.append(`items[${i}][id]`, item.id);
            body.append(`items[${i}][parent_id]`, item.parent_id);
        });
        try {
            const response = await request(builder.dataset.reorderUrl, { method: 'POST', body });
            toast.success(response.message);
        } catch (error) {
            toast.error(error.message);
        }
    }
}
