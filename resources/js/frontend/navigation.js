/**
 * Header behaviour: mobile drawer, accessible dropdowns and search overlay.
 */
export function initNavigation() {
    const drawer = document.getElementById('mobile-nav');
    const toggles = document.querySelectorAll('[data-nav-toggle]');

    const setDrawer = (open) => {
        if (!drawer) return;
        drawer.hidden = !open;
        document.body.classList.toggle('overflow-hidden', open);
        toggles.forEach((t) => t.setAttribute('aria-expanded', String(open)));
        if (open) drawer.querySelector('a, button')?.focus();
    };

    toggles.forEach((toggle) => toggle.addEventListener('click', () => setDrawer(drawer?.hidden)));
    drawer?.addEventListener('click', (e) => e.target === drawer && setDrawer(false));

    // Desktop dropdowns: open on hover (CSS) and on click/keyboard (JS).
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const button = dropdown.querySelector('[data-dropdown-button]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        if (!button || !menu) return;

        const set = (open) => {
            menu.hidden = !open;
            button.setAttribute('aria-expanded', String(open));
        };
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = menu.hidden;
            document.querySelectorAll('[data-dropdown-menu]').forEach((m) => m !== menu && (m.hidden = true));
            set(willOpen);
        });
        dropdown.addEventListener('mouseenter', () => window.matchMedia('(hover: hover)').matches && set(true));
        dropdown.addEventListener('mouseleave', () => window.matchMedia('(hover: hover)').matches && set(false));
        dropdown.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                set(false);
                button.focus();
            }
        });
    });

    document.addEventListener('click', () => document.querySelectorAll('[data-dropdown-menu]').forEach((m) => (m.hidden = true)));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && drawer && !drawer.hidden && setDrawer(false));

    // Header shadow once the page scrolls.
    const header = document.querySelector('[data-site-header]');
    if (header) {
        const onScroll = () => header.classList.toggle('shadow-md', window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }
}
