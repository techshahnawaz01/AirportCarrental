import { request } from './http';
import { toast } from './toast';
import { confirmDialog } from './dialog';

/**
 * Declarative AJAX buttons:
 * <button data-action="/admin/pages/1" data-method="DELETE"
 *         data-confirm="Move this page to trash?" data-remove="tr">…</button>
 *
 * data-remove="selector"   remove the closest matching element on success
 * data-toggle-target       element whose [data-on]/[data-off] children swap on success (uses data.active)
 * data-refresh             re-load the closest [data-ajax-table]
 */
export function initActions(root = document) {
    root.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button || button.disabled) return;
        event.preventDefault();

        if (button.dataset.confirm) {
            const ok = await confirmDialog({
                title: button.dataset.confirmTitle || 'Are you sure?',
                message: button.dataset.confirm,
                confirmText: button.dataset.confirmButton || 'Confirm',
                tone: button.dataset.tone || 'danger',
            });
            if (!ok) return;
        }

        button.disabled = true;
        button.classList.add('opacity-60');

        try {
            const response = await request(button.dataset.action, { method: button.dataset.method || 'POST' });
            toast.success(response.message);
            button.dispatchEvent(new CustomEvent('action:success', { bubbles: true, detail: response }));

            if (response.data?.redirect || button.dataset.redirectAfter) {
                window.location.assign(response.data?.redirect || button.dataset.redirectAfter);
                return;
            }

            if (button.dataset.remove) {
                const target = button.closest(button.dataset.remove);
                target?.classList.add('opacity-0', 'transition');
                setTimeout(() => {
                    const table = target?.closest('[data-ajax-table]');
                    target?.remove();
                    table?.dispatchEvent(new CustomEvent('rows:changed'));
                }, 200);
            }

            if ('active' in (response.data || {})) {
                const scope = button.closest('[data-toggle-scope]') || button;
                scope.querySelectorAll('[data-on]').forEach((el) => (el.hidden = !response.data.active));
                scope.querySelectorAll('[data-off]').forEach((el) => (el.hidden = response.data.active));
                button.setAttribute('aria-pressed', response.data.active ? 'true' : 'false');
            }

            if (button.hasAttribute('data-refresh')) {
                button.closest('[data-ajax-table]')?.dispatchEvent(new CustomEvent('refresh'));
            }
        } catch (error) {
            toast.error(error.message);
        } finally {
            button.disabled = false;
            button.classList.remove('opacity-60');
        }
    });
}
