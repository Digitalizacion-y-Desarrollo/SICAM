document.querySelectorAll('[data-campo-dinamico-form]').forEach((form) => {
    const type = form.querySelector('[data-campo-tipo]');
    const options = form.querySelector('[data-campo-opciones]');
    const list = form.querySelector('[data-opciones-lista]');

    const updateRemoveButtons = () => {
        const rows = [...list.querySelectorAll('[data-opcion-fila]')];
        rows.forEach((row) => {
            row.querySelector('[data-quitar-opcion]').disabled = rows.length === 1;
        });
    };

    const addOption = () => {
        const row = document.createElement('div');
        row.className = 'flex gap-2';
        row.dataset.opcionFila = '';

        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'opciones[]';
        input.className = 'form-control';
        input.maxLength = 150;
        input.placeholder = 'Nombre de la opción';
        input.required = true;

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'action-button shrink-0 text-red-700';
        remove.dataset.quitarOpcion = '';
        remove.setAttribute('aria-label', 'Quitar opción');
        remove.textContent = 'Quitar';

        row.append(input, remove);
        list.append(row);
        updateRemoveButtons();
        input.focus();
    };

    const updateType = () => {
        const isSelection = type.value === 'SELECCION';
        options.hidden = !isSelection;
        options.disabled = !isSelection;
    };

    type.addEventListener('change', updateType);
    form.querySelector('[data-agregar-opcion]').addEventListener('click', addOption);
    list.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-quitar-opcion]');
        if (!remove || remove.disabled) return;
        remove.closest('[data-opcion-fila]').remove();
        updateRemoveButtons();
    });

    updateRemoveButtons();
    updateType();
});
