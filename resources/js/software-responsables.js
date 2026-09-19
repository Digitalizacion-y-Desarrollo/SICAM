import Swal from 'sweetalert2';

document.querySelectorAll('[data-nuevo-responsable]').forEach((container) => {
    const systemForm = container.closest('form');
    const trigger = container.querySelector('[data-responsable-toggle]');
    const modal = container.querySelector('[data-responsable-modal]');
    const fields = modal.querySelector('[data-responsable-campos]');
    const save = modal.querySelector('[data-responsable-guardar]');
    const cancel = modal.querySelector('[data-responsable-cancelar]');
    const close = modal.querySelector('[data-responsable-cerrar]');
    const error = modal.querySelector('[data-responsable-error]');
    const result = container.querySelector('[data-responsable-resultado]');
    const inputs = [...fields.querySelectorAll('[data-responsable-campo]')];
    const dependency = modal.querySelector('[data-responsable-dependencia]');
    const area = modal.querySelector('[data-responsable-area]');
    const departments = JSON.parse(modal.querySelector('[data-responsable-departamentos]').textContent);
    let pending = false;

    document.body.append(modal);

    const updateAreas = () => {
        const dependencyId = dependency.selectedOptions[0]?.dataset.departamentoId;
        const areas = dependencyId ? departments.filter((item) => String(item.parent_id) === dependencyId) : [];
        area.replaceChildren(new Option(dependencyId ? 'Sin área asignada' : 'Selecciona primero una dependencia', ''));
        areas.forEach((item) => area.add(new Option(item.nombre, item.nombre)));
        area.disabled = !dependencyId || !areas.length;
    };
    const hideModal = () => {
        modal.close();
        fields.disabled = true;
        trigger.focus();
    };

    dependency.addEventListener('change', updateAreas);
    trigger.addEventListener('click', () => {
        if (pending) return;
        fields.disabled = false;
        modal.showModal();
        inputs[0].focus();
    });
    cancel.addEventListener('click', hideModal);
    close.addEventListener('click', hideModal);
    modal.addEventListener('cancel', (event) => {
        if (pending) event.preventDefault();
        else fields.disabled = true;
    });
    systemForm.addEventListener('submit', (event) => {
        if (pending) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    save.addEventListener('click', async () => {
        if (pending) return;
        pending = true;
        error.hidden = true;
        result.textContent = '';
        const original = save.innerHTML;
        save.disabled = cancel.disabled = close.disabled = trigger.disabled = true;
        save.setAttribute('aria-busy', 'true');
        save.innerHTML = '<span class="action-spinner" aria-hidden="true"></span>Guardando…';
        try {
            const data = new FormData();
            inputs.forEach((input) => data.set(input.dataset.responsableCampo, input.value.trim()));
            data.set('_token', systemForm.elements.namedItem('_token')?.value || '');
            const response = await fetch(container.dataset.url, {
                method: 'POST', body: data,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(response.status === 422
                    ? Object.values(payload.errors || {}).flat().join(' ') || 'Revisa los datos del responsable.'
                    : 'No se pudo guardar el responsable. Intenta nuevamente.');
            }
            const responsible = payload.responsable;
            if (!responsible?.id) throw new Error('No se pudo confirmar el registro.');
            for (const role of ['funcional', 'tecnico']) {
                const select = systemForm.elements.namedItem(`responsable_${role}_id`);
                select.add(new Option(responsible.nombre_completo + (responsible.cargo ? ` · ${responsible.cargo}` : ''), responsible.id));
                if (role === container.dataset.nuevoResponsable) select.value = String(responsible.id);
            }
            inputs.forEach((input) => { input.value = ''; });
            updateAreas();
            hideModal();
            result.textContent = `${responsible.nombre_completo} se registró y quedó seleccionado.`;
            await Swal.fire({ icon: 'success', title: 'Responsable registrado', text: result.textContent, confirmButtonColor: '#601633' });
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        } finally {
            pending = false;
            save.disabled = cancel.disabled = close.disabled = trigger.disabled = false;
            save.removeAttribute('aria-busy');
            save.innerHTML = original;
        }
    });
});
