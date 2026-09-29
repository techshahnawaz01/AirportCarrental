import { pickMedia } from './media-picker';

/**
 * Rich text editor (TinyMCE, self-hosted, GPL) loaded only when needed.
 */
export async function initEditors() {
    const fields = document.querySelectorAll('textarea[data-editor]');
    if (!fields.length) return;

    // The core must load first: models, themes and plugins register on the global it creates.
    const { default: tinymce } = await import('tinymce/tinymce');
    await Promise.all([
        import('tinymce/models/dom'),
        import('tinymce/themes/silver'),
        import('tinymce/icons/default'),
        import('tinymce/skins/ui/oxide/skin.js'),
        import('tinymce/skins/ui/oxide/content.js'),
        import('tinymce/skins/content/default/content.js'),
        import('tinymce/skins/ui/oxide-dark/skin.js'),
        import('tinymce/skins/ui/oxide-dark/content.js'),
        import('tinymce/plugins/advlist'),
        import('tinymce/plugins/autolink'),
        import('tinymce/plugins/lists'),
        import('tinymce/plugins/link'),
        import('tinymce/plugins/image'),
        import('tinymce/plugins/table'),
        import('tinymce/plugins/code'),
        import('tinymce/plugins/fullscreen'),
        import('tinymce/plugins/media'),
        import('tinymce/plugins/wordcount'),
        import('tinymce/plugins/autoresize'),
    ]);

    fields.forEach((textarea) => {
        tinymce.init({
            target: textarea,
            license_key: 'gpl',
            skin: document.documentElement.classList.contains('dark') ? 'oxide-dark' : 'oxide',
            content_css: 'default',
            content_style: `body{font-family:Inter,system-ui,sans-serif;font-size:15px;line-height:1.65;max-width:860px;margin:1rem auto;padding:0 1rem}img{max-width:100%;height:auto}table{border-collapse:collapse}td,th{border:1px solid #cbd5e1;padding:6px}`,
            menubar: false,
            branding: false,
            promotion: false,
            min_height: 420,
            max_height: 900,
            autoresize_bottom_margin: 20,
            plugins: 'advlist autolink lists link image table code fullscreen media wordcount autoresize',
            toolbar: 'blocks | bold italic underline | bullist numlist | link library image media table | alignleft aligncenter | blockquote hr | removeformat code fullscreen',
            block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
            convert_urls: false,
            relative_urls: false,
            entity_encoding: 'raw',
            valid_elements: '*[*]',
            invalid_elements: 'script,style,form,input,button,object,embed',
            setup(editor) {
                editor.ui.registry.addButton('library', {
                    icon: 'gallery',
                    tooltip: 'Insert from media library',
                    onAction: async () => {
                        const items = await pickMedia({ multiple: true });
                        items?.forEach((item) => editor.insertContent(`<p><img src="${item.src || item.url}" alt="${(item.alt || item.name).replace(/"/g, '&quot;')}" loading="lazy"></p>`));
                    },
                });
                editor.on('change input undo redo', () => editor.save());
            },
        });
    });

    // Make sure AJAX forms serialise the latest editor content.
    document.addEventListener('ajax:before', () => tinymce.triggerSave(), true);
}
