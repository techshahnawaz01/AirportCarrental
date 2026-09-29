import { request } from '../core/http';
import { toast } from '../core/toast';

/**
 * Media library page: drag & drop / multi-file upload and alt-text editing.
 */
export function initMediaLibrary() {
    const zone = document.querySelector('[data-dropzone]');
    if (!zone) return;
    const input = zone.querySelector('input[type="file"]');
    const table = document.querySelector('[data-ajax-table]');
    const status = zone.querySelector('[data-dropzone-status]');

    const upload = async (files) => {
        if (!files.length) return;
        const body = new FormData();
        [...files].forEach((file) => body.append('files[]', file));
        status.innerHTML = `<span class="spinner"></span> Uploading ${files.length} ${files.length === 1 ? 'file' : 'files'}…`;
        zone.classList.add('pointer-events-none');
        try {
            const response = await request(zone.dataset.url, { method: 'POST', body });
            toast.success(response.message);
            table?.dispatchEvent(new CustomEvent('refresh'));
        } catch (error) {
            toast.error(error.errors ? Object.values(error.errors).flat()[0] : error.message);
        } finally {
            status.textContent = '';
            zone.classList.remove('pointer-events-none');
            input.value = '';
        }
    };

    input.addEventListener('change', () => upload(input.files));
    ['dragenter', 'dragover'].forEach((type) =>
        zone.addEventListener(type, (e) => {
            e.preventDefault();
            zone.classList.add('border-primary', 'bg-primary/5');
        }),
    );
    ['dragleave', 'drop'].forEach((type) =>
        zone.addEventListener(type, (e) => {
            e.preventDefault();
            zone.classList.remove('border-primary', 'bg-primary/5');
        }),
    );
    zone.addEventListener('drop', (e) => upload(e.dataTransfer.files));

    // Details dialog: fill from the clicked tile.
    document.addEventListener('click', (e) => {
        const tile = e.target.closest('[data-media-details]');
        if (!tile) return;
        const item = JSON.parse(tile.dataset.mediaDetails);
        const dialog = document.getElementById('media-details');
        dialog.querySelector('img').src = item.url;
        dialog.querySelector('[data-field="name"]').textContent = item.name;
        dialog.querySelector('[data-field="meta"]').textContent = [item.size, item.dimensions].filter(Boolean).join(' · ');
        dialog.querySelector('[data-field="url"]').value = item.url;
        dialog.querySelector('[data-field="path"]').value = item.path;
        const form = dialog.querySelector('form');
        form.action = tile.dataset.updateUrl;
        form.querySelector('[name="alt"]').value = item.alt || '';
        const del = dialog.querySelector('[data-delete]');
        del.dataset.action = tile.dataset.deleteUrl;
        dialog.showModal();
    });

    document.getElementById('media-details')?.addEventListener('click', async (e) => {
        const copy = e.target.closest('[data-copy]');
        if (!copy) return;
        const field = copy.parentElement.querySelector('input');
        try {
            await navigator.clipboard.writeText(field.value);
            toast.success('Copied to clipboard.');
        } catch {
            field.select();
        }
    });

    document.addEventListener('ajax:success', (e) => {
        if (e.target.closest('#media-details')) {
            document.getElementById('media-details').close();
            table?.dispatchEvent(new CustomEvent('refresh'));
        }
    });

    // After deleting from the details dialog, close it and refresh the grid.
    document.getElementById('media-details')?.addEventListener('action:success', (e) => {
        e.currentTarget.close();
        table?.dispatchEvent(new CustomEvent('refresh'));
    });
}
