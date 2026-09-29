/**
 * Promise-based confirmation dialog built on the native <dialog> element.
 * const ok = await confirmDialog({ title, message, confirmText, tone: 'danger' })
 */
let dialog;

function build() {
    dialog = document.createElement('dialog');
    dialog.className =
        'm-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-surface-raised p-0 text-fg shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm';
    dialog.innerHTML = `
        <form method="dialog" class="p-6">
            <div class="flex gap-4">
                <div data-icon class="flex size-10 shrink-0 items-center justify-center rounded-full"></div>
                <div class="min-w-0">
                    <h2 data-title class="text-base font-semibold"></h2>
                    <p data-message class="mt-1.5 text-sm text-fg-muted"></p>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button value="cancel" class="btn btn-secondary">Cancel</button>
                <button value="confirm" data-confirm class="btn"></button>
            </div>
        </form>`;
    document.body.appendChild(dialog);
}

export function confirmDialog({ title = 'Are you sure?', message = '', confirmText = 'Confirm', tone = 'danger' } = {}) {
    if (!dialog) build();
    dialog.querySelector('[data-title]').textContent = title;
    dialog.querySelector('[data-message]').textContent = message;
    const button = dialog.querySelector('[data-confirm]');
    button.textContent = confirmText;
    button.className = `btn ${tone === 'danger' ? 'btn-danger' : 'btn-primary'}`;
    const icon = dialog.querySelector('[data-icon]');
    icon.className = `flex size-10 shrink-0 items-center justify-center rounded-full ${tone === 'danger' ? 'bg-red-500/10 text-red-600' : 'bg-primary/10 text-primary'}`;
    icon.innerHTML =
        '<svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>';

    return new Promise((resolve) => {
        dialog.returnValue = '';
        dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), { once: true });
        dialog.showModal();
        button.focus();
    });
}

/**
 * Generic modal helpers for markup-defined dialogs: data-modal-open="id" / data-modal-close.
 */
export function initModals(root = document) {
    root.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');
        if (opener) {
            event.preventDefault();
            document.getElementById(opener.dataset.modalOpen)?.showModal();
            return;
        }
        const closer = event.target.closest('[data-modal-close]');
        if (closer) {
            event.preventDefault();
            closer.closest('dialog')?.close();
        }
    });

    // Close when the backdrop is clicked.
    root.addEventListener('mousedown', (event) => {
        if (event.target.tagName === 'DIALOG' && event.target.dataset.dismissible !== 'false') {
            const rect = event.target.getBoundingClientRect();
            const inside = event.clientX >= rect.left && event.clientX <= rect.right && event.clientY >= rect.top && event.clientY <= rect.bottom;
            if (!inside) event.target.close();
        }
    });
}
