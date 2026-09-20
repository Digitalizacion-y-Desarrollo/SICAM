document.querySelectorAll('[data-departamentos-form]').forEach((container) => {
    const dependency = container.querySelector('[data-dependencia-accesos]');
    const area = container.querySelector('[data-area-accesos]');
    const areasList = container.querySelector('[data-areas-accesos]');
    const help = container.querySelector('[data-areas-accesos-ayuda]');
    const data = container.querySelector('[data-departamentos-accesos]');
    const departments = data ? JSON.parse(data.textContent) : [];
    const dependencies = departments.filter((department) => department.parent_id === null);

    const updateAreas = (preserveValue = false) => {
        const selectedDependency = dependencies.find((department) => department.nombre === dependency.value.trim());
        const areas = selectedDependency
            ? departments.filter((department) => department.parent_id === selectedDependency.id)
            : [];
        const currentAreaIsValid = areas.some((department) => department.nombre === area.value.trim());

        if (!preserveValue && !currentAreaIsValid) area.value = '';
        areasList.replaceChildren(...areas.map((department) => {
            const option = document.createElement('option');
            option.value = department.nombre;
            return option;
        }));
        area.disabled = dependencies.length > 0 && (!selectedDependency || !areas.length);
        help.textContent = areas.length
            ? `${areas.length} ${areas.length === 1 ? 'área disponible' : 'áreas disponibles'} para la dependencia seleccionada.`
            : (dependency.value.trim() ? 'La dependencia seleccionada no tiene áreas activas.' : 'Selecciona una dependencia para consultar sus áreas.');
    };

    dependency.addEventListener('input', () => updateAreas());
    updateAreas(true);
});
