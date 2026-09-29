/**
 * Accessible tabs: [data-tabs] > [role=tab][aria-controls] + [role=tabpanel].
 */
export function initTabs(root = document) {
    root.querySelectorAll('[data-tabs]').forEach((tabs) => {
        const buttons = [...tabs.querySelectorAll('[role="tab"]')];
        const select = (button) => {
            buttons.forEach((b) => {
                const active = b === button;
                b.setAttribute('aria-selected', String(active));
                b.tabIndex = active ? 0 : -1;
                const panel = document.getElementById(b.getAttribute('aria-controls'));
                if (panel) panel.hidden = !active;
            });
            tabs.dispatchEvent(new CustomEvent('tabs:change', { detail: button.dataset.value }));
        };

        buttons.forEach((button, i) => {
            button.addEventListener('click', () => select(button));
            button.addEventListener('keydown', (e) => {
                const next = e.key === 'ArrowRight' ? buttons[(i + 1) % buttons.length] : e.key === 'ArrowLeft' ? buttons[(i - 1 + buttons.length) % buttons.length] : null;
                if (next) {
                    next.focus();
                    select(next);
                }
            });
        });

        // Reveal a hidden panel when it contains a field with a validation error.
        tabs.closest('form')?.addEventListener('reveal', (e) => {
            const panel = e.target.closest('[role="tabpanel"]');
            const button = panel && buttons.find((b) => b.getAttribute('aria-controls') === panel.id);
            if (button) select(button);
        });
    });
}
