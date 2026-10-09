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
const text = (value) => (value === null || value === undefined || value === '' || value === 'empty' ? '—' : String(value));
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
            list.innerHTML = '<li class="skeleton h-[72px]"></li>'.repeat(4);
            try {
                const data = await loadFlights(widget.dataset[`${direction}Url`]);
                const term = input.value.trim().toLowerCase();
                const flights = data.flights
                    .filter((f) => !term || [f.flight, f.airline, f.city, f.city_iata].join(' ').toLowerCase().includes(term))
                    .slice(0, 6);

                list.innerHTML = '';
                if (!flights.length) {
                    list.appendChild(el('li', 'rounded-2xl border border-dashed border-line py-8 text-center text-sm text-fg-muted md:col-span-2', data.flights.length ? 'No flights match your search.' : 'Live flight data is not available right now.'));
                }
                flights.forEach((f) => {
                    const li = el('li', 'flex min-w-0 items-center gap-3 rounded-2xl border border-line bg-white p-3 transition hover:border-primary/30 hover:shadow-card');
                    const code = el('span', 'flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-xs font-bold text-primary', text(f.city_iata));
                    const left = el('div', 'min-w-0 flex-1');
                    left.append(el('p', 'truncate font-semibold text-fg', text(f.city)), el('p', 'truncate text-xs text-fg-muted', `${text(f.flight)} · ${text(f.airline)} · Gate ${text(f.gate)}`));
                    const right = el('div', 'text-right shrink-0');
                    right.append(el('p', 'font-mono text-sm font-bold text-fg', time(f.scheduled)), el('span', `badge ${STATUS[f.status] || 'badge-neutral'} capitalize`, f.status));
                    li.append(code, left, right);
                    list.appendChild(li);
                });
            } catch {
                list.innerHTML = '';
                list.appendChild(el('li', 'rounded-2xl border border-dashed border-line py-8 text-center text-sm text-fg-muted md:col-span-2', 'Live flight data is not available right now.'));
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

/* Shared bits for the premium flight tables -------------------------------- */

const STATUS_STYLE = {
    active: ['bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
    landed: ['bg-sky-50 text-sky-700 ring-sky-600/20', 'bg-sky-500'],
    scheduled: ['bg-slate-50 text-slate-700 ring-slate-500/20', 'bg-slate-400'],
    cancelled: ['bg-red-50 text-red-700 ring-red-600/20', 'bg-red-500'],
    diverted: ['bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
    incident: ['bg-red-50 text-red-700 ring-red-600/20', 'bg-red-500'],
};
const AIRLINE_TONES = ['from-sky-500 to-blue-700', 'from-indigo-500 to-violet-700', 'from-emerald-500 to-teal-700', 'from-orange-500 to-red-600', 'from-rose-500 to-pink-700', 'from-cyan-500 to-sky-700', 'from-amber-500 to-orange-700', 'from-slate-500 to-slate-800'];

const statusLabel = (status, direction) => (status === 'active' ? (direction === 'arrivals' ? 'En route' : 'Departed') : status || 'unknown');

const statusPill = (status, direction) => {
    const [tone, dot] = STATUS_STYLE[status] || STATUS_STYLE.scheduled;
    const pill = el('span', `inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset ${tone}`);
    pill.append(el('span', `size-1.5 rounded-full ${dot}`), document.createTextNode(statusLabel(status, direction)));
    return pill;
};

const delayChip = (delay) => (delay ? el('span', 'inline-flex shrink-0 whitespace-nowrap rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800', `+${delay} min`) : null);

const airlineBadge = (f, size = 'size-10') => {
    const code = (f.airline_iata || f.flight || f.airline || '?').slice(0, 2).toUpperCase();
    const hash = [...code].reduce((sum, c) => sum + c.charCodeAt(0), 0);
    return el('span', `flex ${size} shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-xs font-bold text-white shadow-sm ${AIRLINE_TONES[hash % AIRLINE_TONES.length]}`, code);
};

const chip = (label, value) => {
    const box = el('span', 'inline-flex min-w-12 flex-col items-center rounded-xl bg-soft px-2.5 py-1 ring-1 ring-line');
    box.append(el('span', 'text-[10px] font-semibold tracking-wider text-muted uppercase', label), el('span', 'font-bold text-secondary', text(value)));
    return box;
};

const timeBlock = (f) => {
    const wrap = el('div', 'leading-tight');
    const changed = f.estimated && time(f.estimated) !== time(f.scheduled);
    wrap.append(el('p', `font-mono text-base font-bold ${changed ? 'text-muted line-through decoration-1' : 'text-secondary'}`, time(f.scheduled)));
    if (changed) wrap.append(el('p', 'font-mono text-sm font-bold text-accent-ink', time(f.estimated)));
    return wrap;
};

const emptyState = (message) => {
    const box = el('div', 'flex flex-col items-center gap-3 px-6 py-14 text-center');
    const icon = el('span', 'flex size-14 items-center justify-center rounded-2xl bg-primary-soft text-2xl');
    icon.textContent = '✈';
    box.append(icon, el('p', 'max-w-sm text-sm text-muted', message));
    return box;
};

/**
 * Full flight board with quick stats, filters, search, pagination, and a card layout on small screens.
 */
export function initFlightBoards() {
    document.querySelectorAll('[data-flight-board]').forEach(async (board) => {
        const direction = board.dataset.direction || 'departures';
        const arriving = direction === 'arrivals';
        const rowsBody = board.querySelector('[data-rows]');
        const cards = board.querySelector('[data-cards]');
        const filters = board.querySelectorAll('[data-filter]');
        const search = board.querySelector('[data-search]');
        const summary = board.querySelector('[data-summary]');
        const pager = board.querySelector('[data-pager]');
        const updated = board.querySelector('[data-updated]');
        const stats = board.querySelectorAll('[data-stat]');
        const columns = arriving ? 7 : 6;
        const perPage = 20;
        let flights = [];
        let page = 1;
        let quick = '';

        const QUICK = {
            '': () => true,
            active: (f) => f.status === 'active',
            delayed: (f) => f.delay > 0 && f.status !== 'cancelled',
            cancelled: (f) => f.status === 'cancelled',
        };

        const fillSelect = (select, values) => {
            [...new Set(values.filter(Boolean))].sort().forEach((value) => select.appendChild(new Option(select.dataset.filter === 'status' ? statusLabel(value, direction) : value, value)));
        };

        const filtered = () => {
            const term = search.value.trim().toLowerCase();
            const active = Object.fromEntries([...filters].map((f) => [f.dataset.filter, f.value]));
            return flights.filter(
                (f) =>
                    QUICK[quick](f) &&
                    (!active.airline || f.airline === active.airline) &&
                    (!active.status || f.status === active.status) &&
                    (!active.terminal || f.terminal === active.terminal) &&
                    (!term || [f.flight, f.airline, f.city, f.city_iata, f.gate].join(' ').toLowerCase().includes(term)),
            );
        };

        const renderStats = () => {
            stats.forEach((button) => {
                button.querySelector('[data-stat-count]').textContent = flights.filter(QUICK[button.dataset.stat]).length;
                button.setAttribute('aria-pressed', String(button.dataset.stat === quick));
            });
        };

        const row = (f) => {
            const tr = el('tr', 'transition-colors hover:bg-primary-soft/60');
            const flightCell = el('td', 'px-5 py-4');
            const flightWrap = el('div', 'flex items-center gap-3');
            const flightText = el('div', 'min-w-0');
            flightText.append(el('p', 'font-bold text-secondary', text(f.flight)), el('p', 'truncate text-xs text-muted', text(f.airline)));
            flightWrap.append(airlineBadge(f), flightText);
            flightCell.appendChild(flightWrap);

            const route = el('td', 'px-5 py-4');
            const routeWrap = el('div', 'flex items-center gap-2');
            if (f.city_iata) routeWrap.appendChild(el('span', 'rounded-lg bg-secondary px-2 py-1 font-mono text-xs font-bold text-white', f.city_iata));
            routeWrap.appendChild(el('span', 'font-medium text-secondary', text(f.city)));
            route.appendChild(routeWrap);

            const status = el('td', 'px-5 py-4');
            const statusWrap = el('div', 'flex flex-wrap items-center gap-1.5');
            statusWrap.appendChild(statusPill(f.status, direction));
            const delay = delayChip(f.delay);
            if (delay) statusWrap.appendChild(delay);
            status.appendChild(statusWrap);

            const timeCell = el('td', 'px-5 py-4');
            timeCell.appendChild(timeBlock(f));
            tr.append(timeCell, flightCell, route, status, el('td', 'px-5 py-4 text-center font-bold text-secondary', text(f.terminal)), el('td', 'px-5 py-4 text-center font-bold text-secondary', text(f.gate)));
            if (arriving) tr.appendChild(el('td', 'px-5 py-4 text-center font-bold text-secondary', text(f.baggage)));
            return tr;
        };

        const card = (f) => {
            const li = el('li', 'min-w-0 rounded-2xl border border-line bg-white p-4 shadow-sm');
            const top = el('div', 'flex items-start justify-between gap-3');
            const who = el('div', 'flex min-w-0 items-center gap-3');
            const name = el('div', 'min-w-0');
            name.append(el('p', 'font-bold text-secondary', text(f.flight)), el('p', 'truncate text-xs text-muted', text(f.airline)));
            who.append(airlineBadge(f, 'size-11'), name);
            const when = timeBlock(f);
            when.classList.add('text-right');
            top.append(who, when);

            const route = el('div', 'mt-3 flex items-center gap-2 rounded-xl bg-soft px-3 py-2 text-sm');
            route.append(el('span', 'text-xs font-semibold text-muted uppercase', arriving ? 'From' : 'To'));
            if (f.city_iata) route.append(el('span', 'rounded-md bg-secondary px-1.5 py-0.5 font-mono text-[11px] font-bold text-white', f.city_iata));
            route.append(el('span', 'truncate font-medium text-secondary', text(f.city)));

            const bottom = el('div', 'mt-3 flex flex-wrap items-center justify-between gap-2');
            const state = el('div', 'flex flex-wrap items-center gap-1.5');
            state.appendChild(statusPill(f.status, direction));
            const delay = delayChip(f.delay);
            if (delay) state.appendChild(delay);
            const chips = el('div', 'flex gap-1.5');
            chips.append(chip('Term', f.terminal), chip('Gate', f.gate));
            if (arriving) chips.append(chip('Bags', f.baggage));
            bottom.append(state, chips);

            li.append(top, route, bottom);
            return li;
        };

        const render = () => {
            const rows = filtered();
            const pages = Math.max(1, Math.ceil(rows.length / perPage));
            page = Math.min(page, pages);
            rowsBody.innerHTML = '';
            cards.innerHTML = '';
            renderStats();

            if (!rows.length) {
                const message = flights.length ? 'No flights match your filters. Try clearing them.' : 'Live flight data is not available right now. Please check back soon.';
                const tr = el('tr');
                const td = el('td');
                td.colSpan = columns;
                td.appendChild(emptyState(message));
                tr.appendChild(td);
                rowsBody.appendChild(tr);
                const li = el('li', 'rounded-2xl border border-dashed border-line bg-white');
                li.appendChild(emptyState(message));
                cards.appendChild(li);
            }

            rows.slice((page - 1) * perPage, page * perPage).forEach((f) => {
                rowsBody.appendChild(row(f));
                cards.appendChild(card(f));
            });

            summary.textContent = `Showing ${rows.length ? (page - 1) * perPage + 1 : 0}–${Math.min(page * perPage, rows.length)} of ${rows.length} ${rows.length === 1 ? 'flight' : 'flights'}`;
            pager.innerHTML = '';
            if (pages > 1) {
                const go = (target) => () => {
                    page = target;
                    render();
                    board.scrollIntoView({ behavior: 'smooth', block: 'start' });
                };
                const button = (label, target, { disabled = false, current = false, aria } = {}) => {
                    const b = el('button', `inline-flex h-10 min-w-10 items-center justify-center rounded-xl px-3 text-sm font-semibold transition ${current ? 'bg-primary text-white shadow-glow-primary' : 'bg-white text-secondary ring-1 ring-line hover:ring-primary/40 hover:text-primary'} disabled:cursor-not-allowed disabled:opacity-40`, label);
                    b.type = 'button';
                    b.disabled = disabled;
                    if (aria) b.setAttribute('aria-label', aria);
                    if (current) b.setAttribute('aria-current', 'page');
                    b.addEventListener('click', go(target));
                    return b;
                };
                pager.appendChild(button('←', page - 1, { disabled: page === 1, aria: 'Previous page' }));
                const numbers = [...new Set([1, page - 1, page, page + 1, pages])].filter((n) => n >= 1 && n <= pages).sort((a, b) => a - b);
                numbers.forEach((n, i) => {
                    if (i && n - numbers[i - 1] > 1) pager.appendChild(el('span', 'px-1 text-muted', '…'));
                    pager.appendChild(button(String(n), n, { current: n === page }));
                });
                pager.appendChild(button('→', page + 1, { disabled: page === pages, aria: 'Next page' }));
            }
        };

        try {
            const data = await loadFlights(board.dataset.url);
            flights = data.flights;
            if (data.fetched_at && updated) updated.textContent = `Updated ${new Date(data.fetched_at).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })}`;
            filters.forEach((select) => fillSelect(select, flights.map((f) => f[select.dataset.filter])));
            const query = new URLSearchParams(window.location.search).get('search');
            if (query) search.value = query;
        } catch {
            flights = [];
        }

        stats.forEach((button) =>
            button.addEventListener('click', () => {
                quick = button.dataset.stat;
                page = 1;
                render();
            }),
        );
        filters.forEach((f) => f.addEventListener('change', () => ((page = 1), render())));
        search.addEventListener('input', () => ((page = 1), render()));
        board.querySelector('[data-reset]')?.addEventListener('click', () => {
            filters.forEach((f) => (f.value = ''));
            search.value = '';
            quick = '';
            page = 1;
            render();
        });
        render();
    });
}

/**
 * Delays & cancellations: a table per status on desktop, cards on small screens.
 */
export function initDisruptions() {
    document.querySelectorAll('[data-disruptions]').forEach(async (widget) => {
        const tables = widget.querySelectorAll('[data-status]');
        const phone = widget.dataset.phone;
        let data = null;
        let direction = 'arrivals';

        // Without a phone number in Settings the Help column is left out entirely.
        const helpLink = (compact = false) => {
            if (!phone) return null;
            const a = el('a', `inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-xl bg-gradient-to-br from-accent to-accent-dark font-semibold text-on-accent shadow-glow-accent transition hover:-translate-y-px ${compact ? 'px-3 py-2 text-xs' : 'px-4 py-2 text-sm'}`, 'Call for help');
            a.href = `tel:${phone.replace(/[^0-9+]/g, '')}`;
            return a;
        };

        const detail = (status, f) =>
            status === 'delayed'
                ? el('span', 'inline-flex shrink-0 whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800', `+${f.delay ?? 0} min`)
                : el('span', 'shrink-0 font-mono font-bold text-secondary', time(f.scheduled));

        const route = (f) => {
            const wrap = el('div', 'flex min-w-0 flex-1 items-center gap-2');
            if (f.city_iata) wrap.appendChild(el('span', 'shrink-0 rounded-lg bg-secondary px-2 py-1 font-mono text-xs font-bold text-white', f.city_iata));
            wrap.appendChild(el('span', 'truncate font-medium text-secondary', text(f.city)));
            return wrap;
        };

        const flightCell = (f) => {
            const wrap = el('div', 'flex min-w-0 items-center gap-3');
            const name = el('div', 'min-w-0');
            name.append(el('p', 'font-bold text-secondary', text(f.flight)), el('p', 'truncate text-xs text-muted', text(f.airline)));
            wrap.append(airlineBadge(f), name);
            return wrap;
        };

        const render = () => {
            tables.forEach((table) => {
                const status = table.dataset.status;
                const body = table.querySelector('tbody');
                const cards = widget.querySelector(`[data-cards-for="${status}"]`);
                const count = widget.querySelector(`[data-count="${status}"]`);
                const flights = data?.[status]?.[direction]?.flights || [];
                if (count) count.textContent = flights.length;
                body.innerHTML = '';
                cards.innerHTML = '';

                if (!flights.length) {
                    const message = data ? `No ${status} ${direction} reported today.` : 'Live flight data is not available right now.';
                    const tr = el('tr');
                    const td = el('td');
                    td.colSpan = phone ? 4 : 3;
                    td.appendChild(emptyState(message));
                    tr.appendChild(td);
                    body.appendChild(tr);
                    const li = el('li');
                    li.appendChild(emptyState(message));
                    cards.appendChild(li);
                    return;
                }

                flights.forEach((f) => {
                    const tr = el('tr', 'transition-colors hover:bg-soft');
                    const cells = [flightCell(f), route(f), detail(status, f), helpLink(true)].filter(Boolean).map((content, i) => {
                        const td = el('td', `px-5 py-4 ${i === 3 ? 'text-right' : ''}`);
                        td.appendChild(content);
                        return td;
                    });
                    tr.append(...cells);
                    body.appendChild(tr);

                    const li = el('li', 'min-w-0 rounded-2xl border border-line bg-white p-4');
                    const top = el('div', 'flex items-start justify-between gap-3');
                    top.append(flightCell(f), detail(status, f));
                    const bottom = el('div', 'mt-3 flex items-center justify-between gap-3 border-t border-line pt-3');
                    bottom.append(...[route(f), helpLink(true)].filter(Boolean));
                    li.append(top, bottom);
                    cards.appendChild(li);
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
