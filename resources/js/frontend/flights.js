import { request } from '../core/http';

const cache = {};
const loadFlights = (url) => (cache[url] ??= request(url).then((r) => r.data));

const STATUS = {
    active: 'badge-success',
    landed: 'badge-primary',
    scheduled: 'badge-neutral',
    cancelled: 'badge-danger',
    diverted: 'badge-warning',
    incident: 'badge-danger',
};

const time = (iso) => (iso ? new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', timeZone: 'UTC' }) : '—');
const text = (value) => (value === null || value === undefined || value === '' ? '—' : String(value));
const el = (tag, className, content) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (content !== undefined) node.textContent = content;
    return node;
};

/**
 * Compact home-page widget: tabs for departures/arrivals + search box.
 */
export function initFlightWidget() {
    document.querySelectorAll('[data-flight-widget]').forEach((widget) => {
        const list = widget.querySelector('[data-flight-list]');
        const input = widget.querySelector('input[type="search"]');
        const more = widget.querySelector('[data-more-link]');
        let direction = 'departures';

        const render = async () => {
            list.innerHTML = '<li class="space-y-2 py-2"><div class="skeleton h-10"></div><div class="skeleton h-10"></div><div class="skeleton h-10"></div></li>';
            try {
                const data = await loadFlights(widget.dataset[`${direction}Url`]);
                const term = input.value.trim().toLowerCase();
                const flights = data.flights
                    .filter((f) => !term || [f.flight, f.airline, f.city, f.city_iata].join(' ').toLowerCase().includes(term))
                    .slice(0, 6);

                list.innerHTML = '';
                if (!flights.length) {
                    list.appendChild(el('li', 'py-8 text-center text-sm text-fg-muted', data.flights.length ? 'No flights match your search.' : 'Live flight data is not available right now.'));
                }
                flights.forEach((f) => {
                    const li = el('li', 'flex items-center justify-between gap-3 py-3');
                    const left = el('div', 'min-w-0');
                    left.append(el('p', 'truncate font-semibold text-fg', `${text(f.flight)} · ${text(f.city)}`), el('p', 'truncate text-xs text-fg-muted', `${f.airline} · Terminal ${text(f.terminal)} · Gate ${text(f.gate)}`));
                    const right = el('div', 'text-right shrink-0');
                    right.append(el('p', 'font-mono text-sm font-semibold text-fg', time(f.scheduled)), el('span', `badge ${STATUS[f.status] || 'badge-neutral'} capitalize`, f.status));
                    li.append(left, right);
                    list.appendChild(li);
                });
            } catch {
                list.innerHTML = '';
                list.appendChild(el('li', 'py-8 text-center text-sm text-fg-muted', 'Live flight data is not available right now.'));
            }
        };

        widget.querySelector('[data-tabs]')?.addEventListener('tabs:change', (e) => {
            direction = e.detail;
            input.placeholder = `Search ${direction}…`;
            if (more) {
                more.href = widget.dataset[`${direction}Page`] || '#';
                more.textContent = `View all ${direction} →`;
                more.hidden = !widget.dataset[`${direction}Page`];
            }
            render();
        });

        let timer;
        input?.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(render, 200);
        });
        widget.querySelector('form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            render();
        });

        render();
    });
}

/**
 * Full flight board with filters, search and client-side pagination.
 */
export function initFlightBoards() {
    document.querySelectorAll('[data-flight-board]').forEach(async (board) => {
        const body = board.querySelector('tbody');
        const filters = board.querySelectorAll('[data-filter]');
        const search = board.querySelector('[data-search]');
        const summary = board.querySelector('[data-summary]');
        const pager = board.querySelector('[data-pager]');
        const updated = board.querySelector('[data-updated]');
        const perPage = 20;
        let flights = [];
        let page = 1;

        const fillSelect = (select, values) => {
            [...new Set(values.filter(Boolean))].sort().forEach((value) => select.appendChild(new Option(value, value)));
        };

        const filtered = () => {
            const term = search.value.trim().toLowerCase();
            const active = Object.fromEntries([...filters].map((f) => [f.dataset.filter, f.value]));
            return flights.filter(
                (f) =>
                    (!active.airline || f.airline === active.airline) &&
                    (!active.status || f.status === active.status) &&
                    (!active.terminal || f.terminal === active.terminal) &&
                    (!term || [f.flight, f.airline, f.city, f.city_iata, f.gate].join(' ').toLowerCase().includes(term)),
            );
        };

        const render = () => {
            const rows = filtered();
            const pages = Math.max(1, Math.ceil(rows.length / perPage));
            page = Math.min(page, pages);
            body.innerHTML = '';

            if (!rows.length) {
                const tr = el('tr');
                const td = el('td', 'py-12 text-center text-fg-muted', flights.length ? 'No flights match your filters.' : 'Live flight data is not available right now. Please check back soon.');
                td.colSpan = 8;
                tr.appendChild(td);
                body.appendChild(tr);
            }

            rows.slice((page - 1) * perPage, page * perPage).forEach((f) => {
                const tr = el('tr');
                const status = el('span', `badge ${STATUS[f.status] || 'badge-neutral'} capitalize`, f.status);
                const statusCell = el('td');
                statusCell.appendChild(status);
                tr.append(
                    el('td', 'font-mono font-semibold', time(f.scheduled)),
                    el('td', 'font-semibold', text(f.flight)),
                    el('td', '', f.airline),
                    el('td', '', `${text(f.city)}${f.city_iata ? ` (${f.city_iata})` : ''}`),
                    statusCell,
                    el('td', '', f.delay ? `${f.delay} min` : '—'),
                    el('td', '', text(f.terminal)),
                    el('td', '', text(f.gate)),
                );
                body.appendChild(tr);
            });

            summary.textContent = `${rows.length} ${rows.length === 1 ? 'flight' : 'flights'}`;
            pager.innerHTML = '';
            if (pages > 1) {
                const button = (label, target, disabled) => {
                    const b = el('button', 'btn btn-secondary btn-sm', label);
                    b.type = 'button';
                    b.disabled = disabled;
                    b.addEventListener('click', () => {
                        page = target;
                        render();
                        board.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                    return b;
                };
                pager.append(button('Previous', page - 1, page === 1), el('span', 'text-sm text-fg-muted', `Page ${page} of ${pages}`), button('Next', page + 1, page === pages));
            }
        };

        try {
            const data = await loadFlights(board.dataset.url);
            flights = data.flights;
            if (data.fetched_at && updated) updated.textContent = `Updated ${new Date(data.fetched_at).toLocaleString()}`;
            filters.forEach((select) => fillSelect(select, flights.map((f) => f[select.dataset.filter])));
            const query = new URLSearchParams(window.location.search).get('search');
            if (query) search.value = query;
        } catch {
            flights = [];
        }

        filters.forEach((f) => f.addEventListener('change', () => ((page = 1), render())));
        search.addEventListener('input', () => ((page = 1), render()));
        board.querySelector('[data-reset]')?.addEventListener('click', () => {
            filters.forEach((f) => (f.value = ''));
            search.value = '';
            page = 1;
            render();
        });
        render();
    });
}

/**
 * Delays & cancellations tables.
 */
export function initDisruptions() {
    document.querySelectorAll('[data-disruptions]').forEach(async (widget) => {
        const tables = widget.querySelectorAll('[data-status]');
        const phone = widget.dataset.phone;
        let data = null;
        let direction = 'arrivals';

        const render = () => {
            tables.forEach((table) => {
                const status = table.dataset.status;
                const body = table.querySelector('tbody');
                const count = widget.querySelector(`[data-count="${status}"]`);
                const flights = data?.[status]?.[direction]?.flights || [];
                if (count) count.textContent = flights.length;
                body.innerHTML = '';

                if (!flights.length) {
                    const tr = el('tr');
                    const td = el('td', 'py-8 text-center text-fg-muted', data ? `No ${status} ${direction} reported today.` : 'Live flight data is not available right now.');
                    td.colSpan = 5;
                    tr.appendChild(td);
                    body.appendChild(tr);
                    return;
                }
                flights.forEach((f) => {
                    const tr = el('tr');
                    const help = el('td');
                    if (phone) {
                        const a = el('a', 'font-semibold text-primary hover:underline', 'Call for help');
                        a.href = `tel:${phone.replace(/[^0-9+]/g, '')}`;
                        help.appendChild(a);
                    } else {
                        help.textContent = '—';
                    }
                    tr.append(el('td', 'font-semibold', text(f.flight)), el('td', '', text(f.city)), el('td', '', f.airline), el('td', '', status === 'delayed' ? `${f.delay ?? 0} min` : time(f.scheduled)), help);
                    body.appendChild(tr);
                });
            });
        };

        widget.querySelector('[data-tabs]')?.addEventListener('tabs:change', (e) => {
            direction = e.detail;
            render();
        });

        try {
            data = (await request(widget.dataset.url)).data;
        } catch {
            data = null;
        }
        widget.querySelectorAll('[data-loading]').forEach((n) => n.remove());
        render();
    });
}
