import { initAjaxForms } from './core/forms';
import { initActions } from './core/actions';
import { initModals } from './core/dialog';
import { showFlashMessages } from './core/toast';
import { initTabs } from './frontend/tabs';
import { initLayout } from './admin/layout';
import { initAjaxTables } from './admin/ajax-table';
import { initImageFields } from './admin/image-field';
import { initGalleryFields } from './admin/gallery-field';
import { initRepeaters } from './admin/repeater';
import { initSlugFields } from './admin/slug';
import { initEditors } from './admin/editor';
import { initMediaLibrary } from './admin/media-library';
import { initMenuBuilder } from './admin/menu-builder';

document.addEventListener('DOMContentLoaded', () => {
    initLayout();
    initAjaxForms();
    initActions();
    initModals();
    initTabs();
    initAjaxTables();
    initImageFields();
    initGalleryFields();
    initRepeaters();
    initSlugFields();
    initMediaLibrary();
    initMenuBuilder();
    initEditors();
    showFlashMessages();

    // Settings/page forms: refresh image previews with the stored URLs after saving.
    document.addEventListener('ajax:success', (event) => {
        const images = event.detail?.data?.images;
        if (images) {
            Object.entries(images).forEach(([key, url]) => {
                event.target.querySelector(`[data-image-field][data-key="${key}"]`)?.dispatchEvent(new CustomEvent('image:saved', { detail: { url } }));
            });
        }
        ['featured_image', 'og_image'].forEach((key) => {
            const item = event.detail?.data?.[key];
            if (item) event.target.querySelector(`[data-image-field][data-key="${key}"]`)?.dispatchEvent(new CustomEvent('image:saved', { detail: { url: item.url, value: item.id } }));
        });
        // Forms that add a row to a list: <form data-prepend-to="#list" data-hide-on-success="#empty">
        const form = event.target;
        if (form.dataset?.prependTo && event.detail?.data?.html) {
            document.querySelector(form.dataset.prependTo)?.insertAdjacentHTML('afterbegin', event.detail.data.html);
            document.querySelector(form.dataset.hideOnSuccess)?.classList.add('hidden');
        }

        const url = event.detail?.data?.url;
        if (url) document.querySelectorAll('[data-view-link]').forEach((a) => (a.href = url));
    });

    // Colour inputs: keep the swatch and the hex text field in sync.
    document.addEventListener('input', (event) => {
        const field = event.target.closest('[data-color-field]');
        if (!field) return;
        const picker = field.querySelector('[data-color-picker]');
        const text = field.querySelector('[data-color-text]');
        if (event.target === picker) text.value = picker.value.toLowerCase();
        else if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value;
    });

    // Warn before leaving a form with unsaved changes.
    document.querySelectorAll('form[data-dirty-check]').forEach((form) => {
        let dirty = false;
        form.addEventListener('input', () => (dirty = true));
        form.addEventListener('change', () => (dirty = true));
        form.addEventListener('ajax:success', () => (dirty = false));
        window.addEventListener('beforeunload', (e) => {
            if (dirty) e.preventDefault();
        });
    });
});
