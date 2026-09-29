import { request } from './http';
import { toast } from './toast';

/**
 * AJAX form handling for any <form data-ajax-form>.
 *
 * Options (data attributes on the form):
 *   data-reset          reset the form after success
 *   data-success-event  dispatch a CustomEvent with the response on success
 *   data-no-toast       do not show the success toast
 * A response containing data.redirect navigates; data.reload reloads the page.
 */
export function setLoading(button, loading) {
    if (!button) return;
    if (loading) {
        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML = `<span class="spinner" aria-hidden="true"></span><span>${button.dataset.loadingText || 'Please wait…'}</span>`;
    } else {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
    }
}

/** "values[site_name]" / "faqs[0][question]" → "values.site_name" / "faqs.0.question" */
export const dotted = (name) => name.replace(/\[\]$/, '').replace(/\]\[|\[|\]/g, '.').replace(/\.$/, '');

export function clearErrors(form) {
    form.querySelectorAll('[data-error-for]').forEach((el) => {
        el.textContent = '';
        el.hidden = true;
    });
    form.querySelectorAll('[aria-invalid="true"]').forEach((el) => el.removeAttribute('aria-invalid'));
    form.querySelector('[data-form-alert]')?.setAttribute('hidden', '');
}

export function showErrors(form, errors = {}) {
    let first = null;
    Object.entries(errors).forEach(([key, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;
        const field = [...form.elements].find((el) => el.name && dotted(el.name) === key);
        const slot = form.querySelector(`[data-error-for="${CSS.escape(key)}"]`) || form.querySelector(`[data-error-for="${CSS.escape(key.replace(/\.\d+$/, ''))}"]`);

        if (field) {
            field.setAttribute('aria-invalid', 'true');
            first ??= field;
        }
        if (slot) {
            slot.textContent = message;
            slot.hidden = false;
            first ??= slot;
        }
    });

    const alert = form.querySelector('[data-form-alert]');
    if (alert) {
        const list = Object.values(errors).flat();
        alert.querySelector('[data-form-alert-text]').textContent = list.length > 1 ? `Please fix the ${list.length} highlighted fields.` : list[0] || 'Please fix the errors.';
        alert.removeAttribute('hidden');
    }

    if (first) {
        // Reveal errors inside collapsed tabs/panels before scrolling.
        first.closest('[data-tab-panel]')?.dispatchEvent(new CustomEvent('reveal', { bubbles: true }));
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (typeof first.focus === 'function' && first.tagName !== 'P') first.focus({ preventScroll: true });
    }
}

export function initAjaxForms(root = document) {
    root.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-ajax-form]');
        if (!form) return;
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const submitter = event.submitter || form.querySelector('[type="submit"]');
        form.dispatchEvent(new CustomEvent('ajax:before', { bubbles: true }));
        clearErrors(form);
        setLoading(submitter, true);

        try {
            const response = await request(form.action, {
                method: form.querySelector('input[name="_method"]')?.value || form.method || 'POST',
                body: new FormData(form),
            });

            if (!form.hasAttribute('data-no-toast')) toast.success(response.message);
            if (form.hasAttribute('data-reset') || response.data?.reset) form.reset();
            form.dispatchEvent(new CustomEvent('ajax:success', { bubbles: true, detail: response }));

            if (response.data?.redirect) {
                window.location.assign(response.data.redirect);
                return;
            }
            if (response.data?.reload) {
                window.location.reload();
                return;
            }
        } catch (error) {
            if (error.status === 422) {
                showErrors(form, error.errors);
            }
            toast.error(error.message);
            form.dispatchEvent(new CustomEvent('ajax:error', { bubbles: true, detail: error }));
        } finally {
            setLoading(submitter, false);
        }
    });

    // Clear a field's error as soon as the user edits it.
    root.addEventListener('input', (event) => {
        const field = event.target;
        if (field.getAttribute?.('aria-invalid') !== 'true' || !field.name) return;
        field.removeAttribute('aria-invalid');
        const slot = field.form?.querySelector(`[data-error-for="${CSS.escape(dotted(field.name))}"]`);
        if (slot) slot.hidden = true;
    });
}
