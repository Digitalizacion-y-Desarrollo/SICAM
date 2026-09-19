document.querySelectorAll('[data-asignacion-form]').forEach((form) => {
    const type = form.querySelector('[data-asignacion-tipo]');
    const responsible = form.querySelector('[data-asignacion-responsable]');
    const dependency = form.querySelector('[data-dependencia-accesos]');
    const area = form.querySelector('[data-area-accesos]');

    const setResponsibleLocation = () => {
        const option = responsible.selectedOptions[0];
        dependency.value = option?.value ? option.dataset.dependencia || '' : '';
        dependency.dispatchEvent(new Event('input', { bubbles: true }));
        area.value = option?.value ? option.dataset.area || '' : '';
    };

    const updateType = () => {
        const isPerson = type.value === 'persona';
        responsible.disabled = !isPerson;
        responsible.required = isPerson;
        dependency.readOnly = isPerson;
        area.readOnly = isPerson;

        if (isPerson) setResponsibleLocation();
    };

    responsible.addEventListener('change', setResponsibleLocation);
    type.addEventListener('change', updateType);
    updateType();
});
