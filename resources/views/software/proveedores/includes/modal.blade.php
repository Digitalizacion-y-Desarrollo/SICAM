<div class="mt-2" data-nuevo-proveedor data-url="{{ route('licencia.proveedores.store') }}"
    @if ($recargar ?? false) data-proveedor-recargar @endif>
    <button type="button" class="text-sm font-semibold text-brand hover:underline" data-proveedor-toggle
        aria-haspopup="dialog" aria-controls="nuevo-proveedor">+ Registrar un nuevo proveedor</button>

    <dialog id="nuevo-proveedor" class="responsable-modal" aria-labelledby="titulo-nuevo-proveedor"
        data-proveedor-modal>
        <div class="flex items-center justify-between gap-4 border-b border-line p-5">
            <div>
                <h2 id="titulo-nuevo-proveedor" class="text-lg font-bold">Registrar proveedor o plataforma</h2>
                <p class="mt-1 text-xs text-muted">Agrega servicios como Microsoft, Platzi, Adobe, Canva o Udemy.</p>
            </div>
            <button type="button" class="action-button" data-proveedor-cerrar
                aria-label="Cerrar registro de proveedor">X</button>
        </div>

        <fieldset class="grid gap-4 p-5 sm:grid-cols-2" data-proveedor-campos disabled>
            <label class="block sm:col-span-2">
                <span class="form-label">Nombre del proveedor o plataforma *</span>
                <input type="text" class="form-control" data-proveedor-campo="nombre" maxlength="191"
                    placeholder="Ej. Microsoft, Platzi, Adobe o Canva" autocomplete="organization" required>
            </label>

            <label class="block">
                <span class="form-label">Sitio web</span>
                <input type="url" class="form-control" data-proveedor-campo="sitio_web" maxlength="2048"
                    placeholder="https://www.platzi.com" autocomplete="url">
            </label>

            <label class="block">
                <span class="form-label">Correo de contacto</span>
                <input type="email" class="form-control" data-proveedor-campo="contacto_email" maxlength="191"
                    placeholder="soporte@proveedor.com" autocomplete="email">
            </label>

            <label class="block sm:col-span-2">
                <span class="form-label">Nota adicional</span>
                <textarea class="form-control h-auto min-h-24 p-3" rows="3" maxlength="5000"
                    data-proveedor-campo="observaciones" placeholder="Ej. Plataforma utilizada para capacitación del personal"></textarea>
            </label>

            <p class="text-sm text-red-700 sm:col-span-2" role="alert" data-proveedor-error hidden></p>
            <div class="flex flex-wrap justify-end gap-2 sm:col-span-2">
                <button type="button" class="action-button action-button-primary" data-proveedor-guardar>Guardar proveedor</button>
                <button type="button" class="action-button" data-proveedor-cancelar>Cancelar</button>
            </div>
        </fieldset>
    </dialog>

    <p class="mt-2 text-xs text-muted" role="status" data-proveedor-resultado></p>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-nuevo-proveedor]').forEach(function(container) {
                if (container.dataset.proveedorInicializado === 'true') return;
                container.dataset.proveedorInicializado = 'true';

                const trigger = container.querySelector('[data-proveedor-toggle]');
                const modal = container.querySelector('[data-proveedor-modal]');
                const fields = modal.querySelector('[data-proveedor-campos]');
                const save = modal.querySelector('[data-proveedor-guardar]');
                const cancel = modal.querySelector('[data-proveedor-cancelar]');
                const close = modal.querySelector('[data-proveedor-cerrar]');
                const error = modal.querySelector('[data-proveedor-error]');
                const result = container.querySelector('[data-proveedor-resultado]');
                const inputs = Array.from(fields.querySelectorAll('[data-proveedor-campo]'));
                let pending = false;

                document.body.append(modal);

                const hideModal = function() {
                    modal.close();
                    fields.disabled = true;
                    trigger.focus();
                };

                trigger.addEventListener('click', function() {
                    if (pending) return;
                    error.hidden = true;
                    fields.disabled = false;
                    modal.showModal();
                    inputs[0].focus();
                });
                cancel.addEventListener('click', hideModal);
                close.addEventListener('click', hideModal);
                modal.addEventListener('cancel', function(event) {
                    if (pending) event.preventDefault();
                    else fields.disabled = true;
                });

                save.addEventListener('click', async function() {
                    if (pending) return;
                    if (!inputs[0].value.trim()) {
                        error.textContent = 'Escribe el nombre del proveedor o plataforma.';
                        error.hidden = false;
                        inputs[0].focus();
                        return;
                    }
                    pending = true;
                    error.hidden = true;
                    result.textContent = '';
                    const original = save.innerHTML;
                    save.disabled = cancel.disabled = close.disabled = trigger.disabled = true;
                    save.setAttribute('aria-busy', 'true');
                    save.innerHTML = '<span class="action-spinner" aria-hidden="true"></span>Guardando…';

                    try {
                        const data = new FormData();
                        inputs.forEach(function(input) {
                            data.set(input.dataset.proveedorCampo, input.value.trim());
                        });
                        data.set('activo', '1');
                        data.set('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

                        const response = await fetch(container.dataset.url, {
                            method: 'POST',
                            body: data,
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                        });
                        const payload = await response.json().catch(function() {
                            return {};
                        });

                        if (!response.ok) {
                            const validationErrors = Object.values(payload.errors || {}).flat().join(' ');
                            throw new Error(response.status === 422
                                ? validationErrors || 'Revisa los datos del proveedor.'
                                : 'No se pudo guardar el proveedor. Intenta nuevamente.');
                        }

                        const provider = payload.proveedor;
                        if (!provider?.id) throw new Error('No se pudo confirmar el registro.');

                        document.querySelectorAll('select[name="proveedor_id"]').forEach(function(select) {
                            select.add(new Option(provider.nombre, String(provider.id), true, true));
                        });

                        inputs.forEach(function(input) {
                            input.value = '';
                        });
                        hideModal();
                        result.textContent = provider.nombre + ' se registró y quedó seleccionado.';

                        if (container.hasAttribute('data-proveedor-recargar')) {
                            window.location.reload();
                        }
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
        });
    </script>
@endonce
