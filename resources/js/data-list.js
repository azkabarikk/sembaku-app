function initializeList(root) {
    const find = selector => root.querySelector(selector);
    const table = find('table');
    const search = find('[data-search]');
    const size = find('[data-size]');
    const server = root.hasAttribute('data-server');
    const filters = [...root.querySelectorAll('[data-column]')];
    const rows = table ? [...table.tBodies[0].rows].filter(row => !row.querySelector('td[colspan]'))
        : [...root.querySelectorAll('[data-product-card]')];
    const headers = table ? [...table.tHead.rows[0].cells] : [];
    const normalize = value => value.trim().replace(/\s+/g, ' ').toLocaleLowerCase('id-ID');
    const items = rows.map(row => {
        const text = table ? [...row.cells]
            .filter((_, index) => headers[index]?.textContent.trim() !== 'Aksi')
            .map(cell => cell.textContent).join(' ') : row.dataset.name;
        return { row, text: normalize(text) };
    });
    const valueFor = (row, control) => {
        const cell = row.cells[Number(control.dataset.column)];
        const value = cell.dataset.filterValue ?? (cell.querySelector('[data-filter-label]') ?? cell).textContent.trim();
        return control.type === 'date' ? value.slice(0, 10) : value;
    };
    for (const filter of filters) {
        if (filter.tagName === 'SELECT') {
            const choices = [...new Set(rows.map(row => valueFor(row, filter)))].sort((a, b) => a.localeCompare(b, 'id'));
            for (const choice of choices) filter.add(new Option(choice, choice));
        }
    }
    let page = Number(root.dataset.page);
    let matches = items;
    const prev = find('[data-prev]');
    const next = find('[data-next]');
    const pageNumbers = find('[data-page-numbers]');
    let useLaravelLinks = !!size && prev.tagName === 'A';
    function renderPageNumbers(pages) {
        if (!pageNumbers) return;

        const firstMiddlePage = Math.min(Math.max(page - 2, 2), pages - 5);
        const visiblePages = pages <= 7
            ? Array.from({length: pages}, (_, index) => index + 1)
            : [1, ...Array.from({length: 5}, (_, index) => firstMiddlePage + index), pages];
        const controls = [];
        let previousPage = 0;

        for (const number of visiblePages) {
            if (number - previousPage > 1) {
                const gap = document.createElement('span');
                gap.textContent = '…';
                gap.setAttribute('aria-hidden', 'true');
                controls.push(gap);
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'md3-action md3-button md3-button--outlined md3-button--small';
            button.dataset.goPage = number;
            button.setAttribute('aria-label', 'Halaman ' + number);
            if (number === page) button.setAttribute('aria-current', 'page');
            button.textContent = number;
            controls.push(button);
            previousPage = number;
        }

        pageNumbers.replaceChildren(...controls);
    }
    function setAvailable(control, available) {
        if (control.tagName === 'A') {
            control.setAttribute('aria-disabled', String(!available));
            control.tabIndex = available ? 0 : -1;
            if (available && !useLaravelLinks) {
                control.setAttribute('href', '#');
            } else if (!available) {
                control.removeAttribute('href');
            }
        } else {
            control.disabled = !available;
        }
    }
    function render() {
        const limit = Number(size?.value ?? root.dataset.perPage);
        const pages = Math.max(1, Math.ceil(matches.length / limit));
        if (!server) page = Math.min(Math.max(page, 1), pages);
        const start = server ? 0 : (page - 1) * limit;
        const shown = new Set(matches.slice(start, start + limit).map(item => item.row));
        for (const {row} of items) row.hidden = !shown.has(row);
        find('[data-empty]').hidden = matches.length !== 0 || items.length === 0;
        const count = matches.length;
        const offset = server ? (page - 1) * limit : start;
        const range = count ? `${offset + 1}–${offset + shown.size}` : '0';
        let summary = `${range} dari ${count} data`;
        if (server) {
            summary = search.value.trim() ? `${count} hasil dari ${items.length} data di halaman ${page}`
                : `${range} · ${count} data di halaman ini`;
        } else if (count !== items.length) {
            summary += ` (total ${items.length})`;
        }
        find('[data-range]').textContent = summary;
        if (!server) {
            find('[data-pager]').hidden = pages <= 1;
            find('[data-page-label]').textContent = 'Halaman ' + page + ' dari ' + pages;
            setAvailable(prev, page > 1);
            setAvailable(next, page < pages);
            renderPageNumbers(pages);
        }
    }
    function filterItems() {
        const term = normalize(search.value);
        matches = items.filter(({row, text}) => (text.includes(term) || row.dataset.id === term)
            && filters.every(control => !control.value || valueFor(row, control) === control.value));
    }
    function reset() {
        useLaravelLinks = false;
        if (!server) page = 1;
        filterItems();
        render();
    }
    search.addEventListener('input', reset);
    search.addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
    for (const control of [...filters, ...(size ? [size] : [])]) control.addEventListener('change', reset);
    if (!server) {
        pageNumbers?.addEventListener('click', event => {
            const button = event.target.closest('[data-go-page]');
            if (!button) return;
            page = Number(button.dataset.goPage);
            render();
            pageNumbers.querySelector('[aria-current="page"]')?.focus();
        });
        for (const [control, step] of [[prev, -1], [next, 1]]) {
            control.addEventListener('click', event => {
                if (useLaravelLinks) return;
                event.preventDefault();
                page += step;
                render();
            });
        }
        // Tampilkan baris pertama yang perlu diperbaiki meski sedang tersaring
        root.addEventListener('invalid', event => {
            const row = event.target.closest('tr');
            if (!row?.hidden || event.target !== root.querySelector('input:invalid')) return;
            search.value = '';
            for (const filter of filters) filter.value = '';
            filterItems();
            page = Math.floor(rows.indexOf(row) / Number(size.value)) + 1;
            render();
        }, true);
    }
    filterItems();
    const initialRow = rows.find(row => row.id && '#' + row.id === location.hash || row.querySelector('.md3-control--error'));
    if (size && initialRow) {
        useLaravelLinks = false;
        page = Math.floor(rows.indexOf(initialRow) / Number(size.value)) + 1;
    }
    render();
    find('[data-toolbar]').hidden = false;
}

document.querySelectorAll('[data-list]').forEach(initializeList);
