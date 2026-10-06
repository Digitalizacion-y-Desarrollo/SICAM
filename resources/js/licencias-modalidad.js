document.querySelectorAll('[data-modalidad-licencia]').forEach((form) => {
    const modalidad = form.querySelector('[name="modalidad"]');
    const detalle = form.querySelector('[data-modalidad-otra]');
    const input = detalle?.querySelector('[name="modalidad_otro"]');

    if (!modalidad || !detalle || !input) return;

    const update = () => {
        const esOtra = modalidad.value === 'otra';
        detalle.hidden = !esOtra;
        input.disabled = !esOtra;
        input.required = esOtra;
        if (!esOtra) input.value = '';
    };

    modalidad.addEventListener('change', update);
    update();
});
