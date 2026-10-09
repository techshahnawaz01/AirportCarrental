/**
 * Shows admin dates in the editor's own timezone.
 *
 * - <input type="datetime-local" data-utc="ISO"> gets its value in local time, and the form's
 *   <input name="client_timezone"> is filled so the server reads the value back the same way.
 * - <time data-local-time="ISO"> gets a short local date/time label (also in AJAX-loaded tables).
 */
const pad = (n) => String(n).padStart(2, '0');
const toInputValue = (date) =>
    `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
const label = (date) => date.toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });

function apply(root) {
    const zone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';

    root.querySelectorAll('input[name="client_timezone"]').forEach((input) => (input.value = zone));
    root.querySelectorAll('[data-timezone-name]').forEach((el) => (el.textContent = zone || 'your timezone'));

    root.querySelectorAll('input[data-utc]').forEach((input) => {
        if (input.dataset.localised) return;
        input.dataset.localised = '1';
        const date = input.dataset.utc ? new Date(input.dataset.utc) : null;
        if (date && !Number.isNaN(date.getTime())) input.value = toInputValue(date);
    });

    root.querySelectorAll('[data-local-time]').forEach((el) => {
        const date = new Date(el.dataset.localTime);
        if (!Number.isNaN(date.getTime())) el.textContent = label(date);
    });
}

export function initLocalTimes() {
    apply(document);
    new MutationObserver((mutations) => mutations.forEach((m) => m.addedNodes.forEach((node) => node.nodeType === 1 && apply(node)))).observe(document.body, {
        childList: true,
        subtree: true,
    });
}
