/**
 * Admin shell: responsive sidebar, user menu and light/dark theme toggle.
 */
export function initLayout() {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-sidebar-overlay');
    const setSidebar = (open) => {
        sidebar?.classList.toggle('-translate-x-full', !open);
        overlay?.classList.toggle('hidden', !open);
        document.querySelectorAll('[data-sidebar-toggle]').forEach((b) => b.setAttribute('aria-expanded', String(open)));
    };
    document.querySelectorAll('[data-sidebar-toggle]').forEach((b) => b.addEventListener('click', () => setSidebar(sidebar.classList.contains('-translate-x-full'))));
    overlay?.addEventListener('click', () => setSidebar(false));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && setSidebar(false));

    // Generic popover menus: [data-menu] > [data-menu-button] + [data-menu-panel]
    document.querySelectorAll('[data-menu]').forEach((menu) => {
        const button = menu.querySelector('[data-menu-button]');
        const panel = menu.querySelector('[data-menu-panel]');
        button?.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.hidden = !panel.hidden;
            button.setAttribute('aria-expanded', String(!panel.hidden));
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('[data-menu-panel]').forEach((p) => (p.hidden = true)));

    document.querySelectorAll('[data-theme-toggle]').forEach((button) =>
        button.addEventListener('click', () => {
            const dark = !document.documentElement.classList.contains('dark');
            document.documentElement.classList.toggle('dark', dark);
            try {
                localStorage.setItem('admin-theme', dark ? 'dark' : 'light');
            } catch {
                /* storage unavailable */
            }
        }),
    );
}
