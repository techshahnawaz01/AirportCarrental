import { pickMedia } from './media-picker';
import { request } from '../core/http';
import { toast } from '../core/toast';
import { confirmDialog } from '../core/dialog';

/**
 * Image field with preview, upload, "choose from library" and remove.
 * Markup: resources/views/components/ui/image-field.blade.php
 */
export function initImageFields(root = document) {
    root.querySelectorAll('[data-image-field]').forEach((field) => {
        if (field.dataset.bound) return;
        field.dataset.bound = '1';

        const hidden = field.querySelector('[data-image-value]');
        const file = field.querySelector('input[type="file"]');
        const preview = field.querySelector('[data-image-preview]');
        const empty = field.querySelector('[data-image-empty]');
        const remove = field.querySelector('[data-image-remove]');
        const name = field.querySelector('[data-image-name]');
        const valueKey = field.dataset.valueKey || 'id';
        const alt = field.querySelector('[data-image-alt]');
        const altRow = field.querySelector('[data-image-alt-row]');

        const show = (url, label = '') => {
            preview.src = url || '';
            preview.hidden = !url;
            empty.hidden = !!url;
            remove.hidden = !url;
            if (altRow) altRow.hidden = !url;
            if (name) name.textContent = label;
        };

        // A new file sends its alt text with the form; a stored image saves it straight to the media library.
        const pending = () => !!file?.files.length;
        const syncAltName = () => alt && (pending() ? (alt.name = `_alt[${field.dataset.altKey}]`) : alt.removeAttribute('name'));
        const setMedia = (id, text) => {
            field.dataset.mediaId = id ?? '';
            if (alt && text !== undefined) {
                alt.value = text ?? '';
                alt.dataset.saved = alt.value;
            }
        };
        if (alt) alt.dataset.saved = alt.value;

        alt?.addEventListener('change', async () => {
            const id = field.dataset.mediaId;
            if (pending() || !id || alt.value.trim() === alt.dataset.saved) return;
            try {
                await request(field.dataset.altUrl.replace('__ID__', id), { method: 'PUT', body: { alt: alt.value.trim() } });
                alt.dataset.saved = alt.value.trim();
                toast.success('Alt text saved.');
            } catch (error) {
                toast.error(error.errors ? Object.values(error.errors).flat()[0] : error.message);
            }
        });

        file?.addEventListener('change', () => {
            const selected = file.files[0];
            if (!selected) return;
            const maxKb = Number(field.dataset.maxKb || 0);
            if (maxKb && selected.size > maxKb * 1024) {
                toast.error(`The file must not be larger than ${Math.round(maxKb / 1024) || 1} MB.`);
                file.value = '';
                return;
            }
            show(URL.createObjectURL(selected), `${selected.name} (not saved yet)`);
            if (alt) alt.value = '';
            syncAltName();
            alt?.focus();
        });

        field.querySelector('[data-image-pick]')?.addEventListener('click', async () => {
            const items = await pickMedia();
            if (!items?.length) return;
            if (file) file.value = '';
            syncAltName();
            hidden.value = items[0][valueKey];
            setMedia(items[0].id, items[0].alt);
            show(items[0].url, items[0].name);
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        });

        remove?.addEventListener('click', async () => {
            if (field.dataset.destroyUrl && hidden.value) {
                const ok = await confirmDialog({ title: 'Remove image?', message: 'The image will be removed from this setting. The file stays in the media library.', confirmText: 'Remove' });
                if (!ok) return;
                try {
                    const response = await request(field.dataset.destroyUrl, { method: 'DELETE' });
                    toast.success(response.message);
                } catch (error) {
                    toast.error(error.message);
                    return;
                }
            }
            hidden.value = '';
            if (file) file.value = '';
            syncAltName();
            setMedia('', '');
            show('');
        });

        // Keep the preview in sync after an AJAX save returns stored URLs.
        field.addEventListener('image:saved', (e) => {
            if (file) file.value = '';
            syncAltName();
            if (e.detail?.url) show(e.detail.url, '');
            if (e.detail?.value !== undefined) hidden.value = e.detail.value;
            const id = e.detail?.id ?? (valueKey === 'id' ? e.detail?.value : undefined);
            if (id !== undefined) setMedia(id, e.detail?.alt);
            else if (alt) alt.dataset.saved = alt.value;
        });
    });
}
