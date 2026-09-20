const form = document.querySelector('#registro-bien');

if (form) {
    const category = form.querySelector('#bien-categoria');
    const groups = [...form.querySelectorAll('[data-campos-categoria]')];
    const updateCategory = () => {
        groups.forEach((group) => {
            group.hidden = group.dataset.camposCategoria !== category.value;
            group.disabled = group.hidden;
        });
        const active = groups.find((group) => !group.hidden);
        const empty = form.querySelector('#campos-vacios');
        empty.hidden = !!active?.querySelector('input, select');
        empty.textContent = category.value ? 'Esta categoría no tiene características adicionales.' : 'Selecciona una categoría para ver sus características.';
    };
    category.addEventListener('change', updateCategory);
    updateCategory();

    const person = form.querySelector('#bien-persona');
    const createResponsible = form.querySelector('#bien-crear-responsable');
    const newResponsible = form.querySelector('#bien-nuevo-responsable');
    const responsible = form.querySelector('#bien-responsable');
    const state = form.querySelector('#bien-estado');
    const dependency = form.elements.namedItem('dependencia_id_accesos');
    const area = form.elements.namedItem('area_id_accesos');
    const departmentsData = document.querySelector('#departamentos-accesos-data');
    const departments = departmentsData ? JSON.parse(departmentsData.textContent) : [];
    const dependencies = departments.filter((department) => department.parent_id === null);
    const areasList = form.querySelector('#areas-accesos');
    const areasHelp = form.querySelector('#areas-accesos-ayuda');

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
        areasHelp.textContent = areas.length
            ? `${areas.length} ${areas.length === 1 ? 'área disponible' : 'áreas disponibles'} para la dependencia seleccionada.`
            : (dependency.value.trim() ? 'La dependencia seleccionada no tiene áreas activas.' : 'Selecciona una dependencia para consultar sus áreas.');
    };

    const filterResponsible = () => {
        let count = 0;
        [...responsible.options].forEach((option) => {
            if (!option.value) return;
            const matches = dependency.value.trim() === option.dataset.dependencia && area.value.trim() === option.dataset.area;
            option.hidden = !matches;
            option.disabled = !matches;
            if (matches) count++;
        });
        if (responsible.selectedOptions[0]?.disabled) responsible.value = '';
        form.querySelector('#responsable-ayuda').textContent = count
            ? 'Selecciona un responsable activo de esta dependencia y área.'
            : 'No hay responsables activos para esta dependencia y área. Puedes registrar uno nuevo.';
    };
    const updateResponsibility = () => {
        const mode = form.querySelector('[name="responsabilidad"]:checked').value;
        person.hidden = mode !== 'persona';
        person.disabled = person.hidden;
        form.querySelector('#bien-area').hidden = mode !== 'area';
        newResponsible.hidden = !createResponsible.checked;
        newResponsible.disabled = mode !== 'persona' || !createResponsible.checked;
        responsible.disabled = mode !== 'persona' || createResponsible.checked;
        responsible.required = !responsible.disabled;
        form.querySelector('#bien-responsable-existente').hidden = createResponsible.checked;
        [...state.options].forEach((option) => {
            option.disabled = mode === 'ninguna' ? option.value !== 'DISPONIBLE' : option.value === 'DISPONIBLE';
        });
        if (state.selectedOptions[0]?.disabled) state.value = mode === 'ninguna' ? 'DISPONIBLE' : 'ASIGNADO';
    };
    form.querySelectorAll('[name="responsabilidad"]').forEach((radio) => radio.addEventListener('change', updateResponsibility));
    createResponsible.addEventListener('change', updateResponsibility);
    dependency.addEventListener('input', () => {
        updateAreas();
        filterResponsible();
    });
    area.addEventListener('input', filterResponsible);
    updateAreas(true);
    filterResponsible();
    updateResponsibility();

    let previewUrl;
    const photo = form.querySelector('#bien-fotografia');
    const preview = form.querySelector('#foto-preview');
    photo.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        preview.classList.add('hidden');
        preview.removeAttribute('src');
        const file = photo.files[0];
        const error = file && (!['image/png', 'image/jpeg'].includes(file.type) || file.size > 5 * 1024 * 1024)
            ? 'Selecciona una fotografía JPG o PNG de hasta 5 MB.' : '';
        photo.setCustomValidity(error);
        form.querySelector('#foto-error').textContent = error;
        if (file && !error) {
            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.classList.remove('hidden');
        }
    });
    window.addEventListener('pageshow', () => {
        const button = form.querySelector('[type="submit"]');
        button.disabled = category.options.length < 2;
        button.textContent = 'Guardar bien';
    });
}
