const dialog = document.querySelector('[data-global-search-dialog]');

if (dialog) {
    const panel = dialog.querySelector('[data-global-search-panel]');
    const input = dialog.querySelector('[data-global-search-input]');
    const results = dialog.querySelector('[data-global-search-results]');
    const openButtons = document.querySelectorAll('[data-global-search-open]');
    const closeButtons = dialog.querySelectorAll('[data-global-search-close]');
    const searchUrl = dialog.dataset.searchUrl;
    let debounceTimer;
    let requestController;
    let selectedIndex = -1;
    let lastFocused;

    const resultLinks = () => [...results.querySelectorAll('[data-search-result]')];

    const selectResult = (index) => {
        const links = resultLinks();

        if (!links.length) {
            selectedIndex = -1;
            return;
        }

        selectedIndex = (index + links.length) % links.length;
        links.forEach((link, currentIndex) => {
            const selected = currentIndex === selectedIndex;
            link.setAttribute('aria-selected', String(selected));
            link.classList.toggle('bg-brand/[.07]', selected);
        });
        links[selectedIndex].scrollIntoView({ block: 'nearest' });
    };

    const showMessage = (title, detail) => {
        results.replaceChildren();
        const wrapper = document.createElement('div');
        wrapper.className = 'px-4 py-10 text-center';

        const heading = document.createElement('p');
        heading.className = 'text-sm font-semibold text-ink';
        heading.textContent = title;

        const description = document.createElement('p');
        description.className = 'mt-1 text-xs text-muted';
        description.textContent = detail;

        wrapper.append(heading, description);
        results.append(wrapper);
        selectedIndex = -1;
    };

    const renderResults = (items, query) => {
        results.replaceChildren();
        selectedIndex = -1;

        if (!items.length) {
            showMessage('No encontramos resultados', `No hay coincidencias para “${query}”.`);
            return;
        }

        const count = document.createElement('p');
        count.className = 'px-3 pb-2 pt-1 text-[10px] font-semibold uppercase tracking-wide text-muted';
        count.textContent = `${items.length} ${items.length === 1 ? 'resultado' : 'resultados'}`;
        results.append(count);

        items.forEach((item, index) => {
            const link = document.createElement('a');
            link.href = item.url;
            link.dataset.searchResult = '';
            link.className = 'flex items-center gap-3 rounded-xl px-3 py-3 outline-none transition hover:bg-brand/[.07] focus:bg-brand/[.07]';
            link.setAttribute('role', 'option');
            link.setAttribute('aria-selected', 'false');
            link.addEventListener('mouseenter', () => selectResult(index));

            const badge = document.createElement('span');
            badge.className = 'inline-flex min-w-20 justify-center rounded-md bg-brand/[.08] px-2 py-1 text-[10px] font-semibold text-brand';
            badge.textContent = item.tipo;

            const text = document.createElement('span');
            text.className = 'min-w-0 flex-1';

            const title = document.createElement('strong');
            title.className = 'block truncate text-sm text-ink';
            title.textContent = item.titulo;

            const detail = document.createElement('span');
            detail.className = 'mt-0.5 block truncate text-xs text-muted';
            detail.textContent = item.detalle || 'Sin información adicional';

            const arrow = document.createElement('span');
            arrow.className = 'text-lg text-muted';
            arrow.setAttribute('aria-hidden', 'true');
            arrow.textContent = '→';

            text.append(title, detail);
            link.append(badge, text, arrow);
            results.append(link);
        });
    };

    const search = async (query) => {
        requestController?.abort();
        requestController = new AbortController();
        showMessage('Buscando…', 'Consultando los módulos autorizados.');

        try {
            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });

            if (!response.ok) throw new Error('Search request failed');

            const data = await response.json();
            renderResults(data.resultados || [], query);
        } catch (error) {
            if (error.name !== 'AbortError') {
                showMessage('No se pudo realizar la búsqueda', 'Intenta nuevamente en unos segundos.');
            }
        }
    };

    const open = () => {
        if (!dialog.classList.contains('hidden')) return;

        lastFocused = document.activeElement;
        dialog.classList.remove('hidden');
        dialog.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        window.requestAnimationFrame(() => {
            dialog.classList.remove('opacity-0');
            dialog.classList.add('opacity-100');
            panel?.classList.remove('translate-y-3');
            input?.focus();
            input?.select();
        });
    };

    const close = () => {
        if (dialog.classList.contains('hidden')) return;

        requestController?.abort();
        clearTimeout(debounceTimer);
        dialog.classList.add('opacity-0');
        dialog.classList.remove('opacity-100');
        panel?.classList.add('translate-y-3');
        document.body.classList.remove('overflow-hidden');

        window.setTimeout(() => {
            dialog.classList.add('hidden');
            dialog.classList.remove('flex');
            lastFocused?.focus();
        }, 200);
    };

    openButtons.forEach((button) => button.addEventListener('click', open));
    closeButtons.forEach((button) => button.addEventListener('click', close));

    input?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();

        if (query.length < 2) {
            requestController?.abort();
            showMessage('Busca en todo SICAM', 'Escribe al menos dos caracteres para comenzar.');
            return;
        }

        debounceTimer = window.setTimeout(() => search(query), 250);
    });

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            selectResult(selectedIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            selectResult(selectedIndex - 1);
        } else if (event.key === 'Enter' && selectedIndex >= 0) {
            event.preventDefault();
            resultLinks()[selectedIndex]?.click();
        }
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            open();
        } else if (event.key === 'Escape' && !dialog.classList.contains('hidden')) {
            event.preventDefault();
            close();
        }
    });
}
