document.querySelectorAll('[data-departamentos-form]').forEach((container) => {
    const dependency = container.querySelector('[data-dependencia-accesos]');
    const area = container.querySelector('[data-area-accesos]');
    const areasList = container.querySelector('[data-areas-accesos]') ?? area;
    const help = container.querySelector('[data-areas-accesos-ayuda]');
    const areaField = container.querySelector('[data-area-accesos-field]');
    const data = container.querySelector('[data-departamentos-accesos]');
    if (!dependency || !area || !areasList || !help) return;

    const departments = data ? JSON.parse(data.textContent) : [];
    const dependencies = departments.filter((department) => department.parent_id === null);

    const updateAreas = (preserveValue = false) => {
        const selectedDependency = dependencies.find((department) => String(department.id) === dependency.value.trim());
        const areas = selectedDependency
            ? departments.filter((department) => department.parent_id === selectedDependency.id)
            : [];
        const currentArea = area.value;
        const currentAreaIsValid = areas.some((department) => String(department.id) === currentArea.trim());

        if (!preserveValue && !currentAreaIsValid) area.value = '';
        areasList.replaceChildren(new Option('Selecciona un área', ''), ...areas.map((department) => {
            const option = document.createElement('option');
            option.value = department.id;
            option.textContent = department.nombre;
            return option;
        }));
        area.value = currentAreaIsValid ? currentArea : '';
        area.disabled = dependencies.length > 0 && (!selectedDependency || !areas.length);
        if (areaField) areaField.hidden = areas.length === 0;
        help.textContent = areas.length
            ? `${areas.length} ${areas.length === 1 ? 'área disponible' : 'áreas disponibles'} para la dependencia seleccionada.`
            : (dependency.value.trim() ? 'La dependencia seleccionada no tiene áreas activas.' : 'Selecciona una dependencia para consultar sus áreas.');
    };

    dependency.addEventListener('change', () => updateAreas());
    updateAreas(true);
});
