/**
 * Guide template: reading progress bar and table-of-contents scroll-spy.
 */
export function initGuide() {
    const progress = document.querySelector('[data-reading-progress]');
    const article = document.querySelector('[data-toc] ~ article, article');
    const links = [...document.querySelectorAll('[data-toc-link]')];

    if (progress && article) {
        const update = () => {
            const rect = article.getBoundingClientRect();
            const total = rect.height - window.innerHeight;
            const read = Math.min(Math.max(-rect.top / (total > 0 ? total : 1), 0), 1);
            progress.style.width = `${(read * 100).toFixed(1)}%`;
        };
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    if (!links.length || !('IntersectionObserver' in window)) return;

    const headings = [...new Set(links.map((l) => l.dataset.tocLink))].map((id) => document.getElementById(id)).filter(Boolean);
    const setCurrent = (id) => links.forEach((link) => link.setAttribute('aria-current', String(link.dataset.tocLink === id)));

    const observer = new IntersectionObserver(
        (entries) => {
            const visible = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
            if (visible[0]) setCurrent(visible[0].target.id);
        },
        { rootMargin: '-100px 0px -65% 0px' },
    );
    headings.forEach((heading) => {
        heading.classList.add('scroll-mt-28');
        observer.observe(heading);
    });

    // Close the mobile outline after choosing a section.
    document.querySelectorAll('[data-toc] details a').forEach((a) => a.addEventListener('click', () => a.closest('details').removeAttribute('open')));
}
