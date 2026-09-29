/**
 * Auto-generate a slug from the title until the slug is edited by hand.
 */
export const slugify = (value) =>
    value
        .toString()
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .trim()
        .replace(/&/g, ' and ')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 190);

export function initSlugFields() {
    document.querySelectorAll('[data-slug-source]').forEach((source) => {
        const target = document.querySelector(source.dataset.slugSource);
        if (!target) return;
        let manual = target.value !== '' && target.value !== slugify(source.value);
        target.addEventListener('input', () => {
            manual = target.value !== '';
            target.value = slugify(target.value).replace(/-$/, '') + (target.value.endsWith('-') ? '-' : '');
        });
        target.addEventListener('blur', () => (target.value = slugify(target.value)));
        source.addEventListener('input', () => {
            if (!manual) target.value = slugify(source.value);
            target.dispatchEvent(new Event('slug:changed'));
        });
    });

    // Live URL preview
    document.querySelectorAll('[data-url-preview]').forEach((preview) => {
        const slug = document.querySelector(preview.dataset.slug);
        const parent = document.querySelector(preview.dataset.parent);
        const update = () => {
            const parentPath = parent?.selectedOptions[0]?.dataset.path;
            preview.textContent = `${preview.dataset.base}/${parentPath ? `${parentPath}/` : ''}${slug.value}`;
        };
        slug?.addEventListener('input', update);
        slug?.addEventListener('slug:changed', update);
        parent?.addEventListener('change', update);
        update();
    });
}
