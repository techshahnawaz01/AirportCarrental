import { request } from '../core/http';
import { toast } from '../core/toast';

/**
 * Media library picker. Opens a modal listing images with search, infinite
 * "load more" and inline upload. Resolves with the selected item(s).
 *
 *   const items = await pickMedia({ multiple: true });
 */
let dialog;
let resolver;
let state = { page: 1, q: '', multiple: false, selected: new Map() };

function template() {
    const node = document.getElementById('media-picker-template');
    dialog = node.content.firstElementChild.cloneNode(true);
    document.body.appendChild(dialog);

    const search = dialog.querySelector('[data-picker-search]');
    let timer;
    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            state.q = search.value.trim();
            load(true);
        }, 300);
    });

    dialog.querySelector('[data-picker-more]').addEventListener('click', () => load(false));
    dialog.querySelector('[data-picker-confirm]').addEventListener('click', () => finish([...state.selected.values()]));
    dialog.querySelector('[data-picker-upload]').addEventListener('change', upload);
    dialog.addEventListener('close', () => {
        if (resolver) resolver(null);
        resolver = null;
    });

    dialog.querySelector('[data-picker-grid]').addEventListener('click', (e) => {
        const tile = e.target.closest('[data-item]');
        if (!tile) return;
        const item = JSON.parse(tile.dataset.item);
        if (!state.multiple) return finish([item]);
        if (state.selected.has(item.id)) state.selected.delete(item.id);
        else state.selected.set(item.id, item);
        tile.setAttribute('aria-pressed', String(state.selected.has(item.id)));
        updateCount();
    });
}

function updateCount() {
    const button = dialog.querySelector('[data-picker-confirm]');
    button.hidden = !state.multiple;
    button.disabled = state.selected.size === 0;
    button.textContent = state.selected.size ? `Insert ${state.selected.size} selected` : 'Select images';
}

function tile(item) {
    const button = document.createElement('button');
    button.type = 'button';
    button.dataset.item = JSON.stringify(item);
    button.setAttribute('aria-pressed', String(state.selected.has(item.id)));
    button.className = 'group relative aspect-square overflow-hidden rounded-lg border border-line bg-surface-muted focus-visible:ring-2 focus-visible:ring-primary aria-pressed:ring-2 aria-pressed:ring-primary aria-pressed:ring-offset-2';
    button.innerHTML = '<img loading="lazy" class="size-full object-cover transition group-hover:scale-105" alt=""><span class="absolute inset-x-0 bottom-0 truncate bg-slate-950/60 px-2 py-1 text-left text-[11px] text-white"></span>';
    button.querySelector('img').src = item.url;
    button.querySelector('img').alt = item.alt || item.name;
    button.querySelector('span').textContent = item.name;
    return button;
}

async function load(reset) {
    const grid = dialog.querySelector('[data-picker-grid]');
    const more = dialog.querySelector('[data-picker-more]');
    const empty = dialog.querySelector('[data-picker-empty]');
    if (reset) {
        state.page = 1;
        grid.innerHTML = Array.from({ length: 8 }, () => '<div class="skeleton aspect-square"></div>').join('');
    }
    try {
        const url = new URL(dialog.dataset.url, window.location.origin);
        url.searchParams.set('page', state.page);
        if (state.q) url.searchParams.set('q', state.q);
        const { data } = await request(url);
        if (reset) grid.innerHTML = '';
        data.items.forEach((item) => grid.appendChild(tile(item)));
        empty.hidden = grid.children.length > 0;
        more.hidden = !data.next_page;
        state.page = data.next_page || state.page;
    } catch (error) {
        toast.error(error.message);
    }
}

async function upload(event) {
    const files = [...event.target.files];
    if (!files.length) return;
    const label = dialog.querySelector('[data-picker-upload-label]');
    const original = label.innerHTML;
    label.innerHTML = '<span class="spinner"></span> Uploading…';
    const body = new FormData();
    files.forEach((file) => body.append('files[]', file));
    try {
        const response = await request(dialog.dataset.uploadUrl, { method: 'POST', body });
        toast.success(response.message);
        const grid = dialog.querySelector('[data-picker-grid]');
        dialog.querySelector('[data-picker-empty]').hidden = true;
        response.data.items.reverse().forEach((item) => grid.prepend(tile(item)));
        if (!state.multiple && response.data.items.length === 1) finish(response.data.items);
    } catch (error) {
        toast.error(error.errors ? Object.values(error.errors).flat()[0] : error.message);
    } finally {
        label.innerHTML = original;
        event.target.value = '';
    }
}

function finish(items) {
    const resolve = resolver;
    resolver = null;
    dialog.close();
    resolve?.(items);
}

export function pickMedia({ multiple = false } = {}) {
    if (!dialog) template();
    state = { page: 1, q: '', multiple, selected: new Map() };
    dialog.querySelector('[data-picker-search]').value = '';
    updateCount();
    load(true);
    dialog.showModal();
    return new Promise((resolve) => (resolver = resolve));
}
