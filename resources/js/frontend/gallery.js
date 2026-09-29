/**
 * Minimal lightbox for [data-gallery] containers with <a href="full.jpg"><img></a> children.
 */
export function initGalleries() {
    const galleries = document.querySelectorAll('[data-gallery]');
    if (!galleries.length) return;

    const dialog = document.createElement('dialog');
    dialog.className = 'm-auto max-h-[90vh] w-[calc(100%-2rem)] max-w-5xl bg-transparent p-0 backdrop:bg-slate-950/85';
    dialog.innerHTML = `
        <figure class="relative">
            <img class="mx-auto max-h-[80vh] w-auto rounded-xl object-contain" alt="">
            <figcaption class="mt-3 text-center text-sm text-white/80"></figcaption>
            <button type="button" data-prev class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-slate-900 shadow" aria-label="Previous image">‹</button>
            <button type="button" data-next class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-slate-900 shadow" aria-label="Next image">›</button>
            <button type="button" data-close class="absolute -top-3 -right-3 rounded-full bg-white p-2 text-slate-900 shadow" aria-label="Close">✕</button>
        </figure>`;
    document.body.appendChild(dialog);

    let items = [];
    let index = 0;
    const img = dialog.querySelector('img');
    const caption = dialog.querySelector('figcaption');
    const show = (i) => {
        index = (i + items.length) % items.length;
        img.src = items[index].href;
        img.alt = items[index].querySelector('img')?.alt || '';
        caption.textContent = img.alt;
    };

    galleries.forEach((gallery) => {
        gallery.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;
            e.preventDefault();
            items = [...gallery.querySelectorAll('a')];
            show(items.indexOf(link));
            dialog.showModal();
        });
    });

    dialog.querySelector('[data-prev]').addEventListener('click', () => show(index - 1));
    dialog.querySelector('[data-next]').addEventListener('click', () => show(index + 1));
    dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });
    dialog.addEventListener('click', (e) => e.target === dialog && dialog.close());
}
