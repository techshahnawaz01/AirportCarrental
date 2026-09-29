/**
 * Fetch wrapper that speaks the app's JSON envelope:
 * { success, message, data } / { success: false, message, errors }.
 */
export class HttpError extends Error {
    constructor(message, status, payload = {}) {
        super(message);
        this.status = status;
        this.errors = payload.errors || {};
        this.payload = payload;
    }
}

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export async function request(url, { method = 'GET', body = null, headers = {}, signal } = {}) {
    const init = {
        method: method.toUpperCase() === 'GET' ? 'GET' : 'POST',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
            ...headers,
        },
        credentials: 'same-origin',
        signal,
    };

    if (body !== null) {
        if (!(body instanceof FormData)) {
            const form = new FormData();
            Object.entries(body).forEach(([key, value]) => form.append(key, value ?? ''));
            body = form;
        }
        // Laravel method spoofing keeps multipart uploads working for PUT/PATCH/DELETE.
        if (!['GET', 'POST'].includes(method.toUpperCase()) && !body.has('_method')) {
            body.append('_method', method.toUpperCase());
        }
        init.body = body;
    } else if (!['GET', 'POST'].includes(method.toUpperCase())) {
        const form = new FormData();
        form.append('_method', method.toUpperCase());
        init.body = form;
    }

    let response;
    try {
        response = await fetch(url, init);
    } catch (error) {
        if (error.name === 'AbortError') throw error;
        throw new HttpError('Network error. Please check your connection and try again.', 0);
    }

    let payload = {};
    try {
        payload = await response.json();
    } catch {
        payload = {};
    }

    if (!response.ok || payload.success === false) {
        throw new HttpError(payload.message || fallbackMessage(response.status), response.status, payload);
    }

    return payload;
}

function fallbackMessage(status) {
    return (
        {
            401: 'Your session has expired. Please sign in again.',
            403: 'You are not allowed to perform this action.',
            404: 'Not found.',
            419: 'Your session has expired. Please refresh the page.',
            422: 'Please fix the errors.',
            429: 'Too many requests. Please try again shortly.',
        }[status] || 'Something went wrong. Please try again.'
    );
}
