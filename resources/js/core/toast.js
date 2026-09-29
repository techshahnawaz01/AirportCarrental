/**
 * Accessible toast notifications. Usage: toast.success('Saved'), toast.error('Oops').
 */
const ICONS = {
    success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    error: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>',
    info: '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>',
};
const TONES = {
    success: 'text-emerald-600',
    error: 'text-red-600',
    info: 'text-primary',
};

function container() {
    let el = document.getElementById('toast-region');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast-region';
        el.setAttribute('aria-live', 'polite');
        el.className = 'pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4 sm:items-end sm:right-4 sm:left-auto';
        document.body.appendChild(el);
    }
    return el;
}

function show(type, message, timeout = 4500) {
    if (!message) return;
    const el = document.createElement('div');
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.className =
        'pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border border-line bg-surface-raised p-4 text-sm text-fg shadow-lg ring-1 ring-black/5 transition duration-300 translate-y-2 opacity-0';
    el.innerHTML = `
        <svg class="size-5 shrink-0 ${TONES[type]}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">${ICONS[type]}</svg>
        <p class="flex-1 leading-5"></p>
        <button type="button" class="-m-1 rounded p-1 text-fg-muted hover:text-fg" aria-label="Dismiss">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
        </button>`;
    el.querySelector('p').textContent = message;

    const remove = () => {
        el.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => el.remove(), 300);
    };
    el.querySelector('button').addEventListener('click', remove);
    container().appendChild(el);
    requestAnimationFrame(() => el.classList.remove('opacity-0', 'translate-y-2'));
    if (timeout) setTimeout(remove, timeout);
}

export const toast = {
    success: (message) => show('success', message),
    error: (message) => show('error', message, 7000),
    info: (message) => show('info', message),
};

/** Flash messages rendered by the server as data attributes. */
export function showFlashMessages() {
    document.querySelectorAll('[data-flash]').forEach((el) => {
        show(el.dataset.flash, el.dataset.message);
        el.remove();
    });
}
