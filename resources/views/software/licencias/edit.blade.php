@extends('layouts.app')

@section('title', 'SICAM | Registrar licencia')
@section('breadcrumb', 'Licencias / Registrar licencia')

@section('content')
    @php($dependenciasAccesos = collect($departamentos ?? [])->whereNull('parent_id'))

    <form class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-7" action="{{ route('licencia.update', $licencia) }}" method="POST"
        novalidate aria-label="Formulario de registro de licencia">
        @csrf
        @method('PUT')



        <header class="mb-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-heading">Módulo de Licencias</p>
                <h1 class="mt-1 text-2xl font-bold text-ink">Editar licencia: {{ $licencia->nombre }}</h1>
                <p class="mt-1 text-sm text-muted">Edita la licencia</p>
            </div>

        </header>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-4">
                <section class="form-card">
                    <h2>Identificación</h2>
                    <p class="mt-1 text-sm text-muted">Información básica para reconocer el acceso, suscripción o software.
                    </p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label><span class="form-label">Clave interna</span>
                            <input value="{{ old('clave', $licencia->clave) }}"
                                class="form-control cursor-not-allowed bg-soft" disabled aria-describedby="clave-ayuda">
                            @error('clave')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                            <span id="clave-ayuda" class="mt-1 block text-xs text-muted">SICAM genera esta clave
                                automáticamente.</span>
                        </label>
                        <label><span class="form-label">Nombre del registro *</span><input name="nombre"
                                value="{{ old('nombre', $licencia->nombre) }}" class="form-control" maxlength="191"
                                placeholder="Ej. Canva Comunicación Social 2026" required>
                            @error('nombre')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                            <span class="mt-1 block text-xs text-muted">
                                Un nombre que permita distinguir esta
                                contratación.</span></label>
                        <label><span class="form-label">Software, plataforma o servicio *</span><input name="producto"
                                value="{{ old('producto', $licencia->producto) }}" class="form-control" maxlength="191"
                                placeholder="Ej. Platzi Business, Microsoft 365 o Adobe CC" required>
                            @error('producto')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <div>
                            <label><span class="form-label">Proveedor o plataforma</span><select name="proveedor_id"
                                    class="form-control">
                                    <option value="">Sin proveedor registrado</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" @selected((string) old('proveedor_id', $licencia->proveedor_id) === (string) $proveedor->id)>
                                            {{ $proveedor->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <span class="mt-1 block text-xs font-medium text-red-600"
                                        role="alert">{{ $message }}</span>
                                @enderror
                            </label>
                            @can('proveedores.crear')
                                @include('software.proveedores.includes.modal')
                            @endcan
                        </div>
                    </div>
                    <label class="mt-4 block"><span class="form-label">Uso o propósito</span>
                        <textarea name="descripcion" class="form-control h-auto min-h-24 p-3" rows="3"
                            placeholder="Ej. Capacitación del personal de TI o diseño de materiales institucionales.">{{ old('descripcion', $licencia->descripcion) }}</textarea>
                        @error('descripcion')
                            <span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>
                        @enderror
                    </label>
                </section>

                <section class="form-card">
                    <h2>Forma de uso</h2>
                    <p class="mt-1 text-sm text-muted">Indica cómo se contrata y cuántas personas o equipos pueden
                        utilizarla.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label><span class="form-label">Tipo de contratación *</span><select name="tipo_licencia"
                                class="form-control" required>
                                <option value="">Selecciona una opción</option>
                                @foreach (\App\Models\Licencia::TIPOS as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('tipo_licencia', $licencia->tipo_licencia) === $valor)>{{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_licencia')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                            <span class="mt-1 block text-xs text-muted">Elige cómo se obtuvo: suscripción, compra, acceso
                                gratuito o prueba.</span></label>
                        <label><span class="form-label">¿Cómo se asigna?</span><select name="modalidad"
                                class="form-control">
                                <option value="">No especificado</option>
                                @foreach (\App\Models\Licencia::MODALIDADES as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('modalidad', $licencia->modalidad) === $valor)>{{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('modalidad')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                            <span class="mt-1 block text-xs text-muted">Por persona, dispositivo, servidor o para toda la
                                institución.</span></label>
                        <label><span class="form-label">Número de accesos o licencias *</span><input type="number"
                                name="cantidad_adquirida"
                                value="{{ old('cantidad_adquirida', $licencia->cantidad_adquirida) }}" class="form-control"
                                min="1" step="1" required>
                            @error('cantidad_adquirida')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label><span class="form-label">Cuenta, folio o número de licencia</span><input
                                name="numero_licencia" value="{{ old('numero_licencia', $licencia->numero_licencia) }}"
                                class="form-control" maxlength="191" autocomplete="off"
                                placeholder="Dato proporcionado por la plataforma o proveedor">
                            @error('numero_licencia')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </section>

                <section class="form-card">
                    <h2>Costo</h2>
                    <p class="mt-1 text-sm text-muted">Importes asociados a la compra o suscripción, si aplican.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label><span class="form-label">Costo por acceso o licencia *</span><input type="number"
                                name="costo_unitario" value="{{ old('costo_unitario', $licencia->costo_unitario) }}"
                                class="form-control" min="0" step="0.01" placeholder="0.00" required>
                            @error('costo_unitario')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label><span class="form-label">Costo total de la contratación *</span><input type="number"
                                name="costo_total" value="{{ old('costo_total', $licencia->costo_total) }}"
                                class="form-control" min="0" step="0.01" placeholder="0.00" required>
                            @error('costo_total')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label><span class="form-label">Moneda *</span><select name="moneda" class="form-control"
                                required>
                                <option value="MXN" @selected(old('moneda', $licencia->moneda) === 'MXN')>MXN · Peso mexicano</option>
                                <option value="USD" @selected(old('moneda', $licencia->moneda) === 'USD')>USD · Dólar estadounidense</option>
                                <option value="EUR" @selected(old('moneda', $licencia->moneda) === 'EUR')>EUR · Euro</option>
                            </select>
                            @error('moneda')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <details class="mt-4 rounded-lg border border-line bg-surface-alt p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-ink">Datos técnicos o contractuales
                            (opcional)</summary>
                        <p class="mt-1 text-xs text-muted">Completa estos datos únicamente cuando existan para este
                            producto.</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label><span class="form-label">Fabricante o editor</span><input name="fabricante"
                                    value="{{ old('fabricante', $licencia->fabricante) }}" class="form-control"
                                    maxlength="150" placeholder="Ej. Microsoft, Adobe o Coursera">
                                @error('fabricante')
                                    <span class="mt-1 block text-xs font-medium text-red-600"
                                        role="alert">{{ $message }}</span>
                                @enderror
                            </label>
                            <label><span class="form-label">Versión o plan</span><input name="version"
                                    value="{{ old('version', $licencia->version) }}" class="form-control"
                                    maxlength="100" placeholder="Ej. Business, Teams o 2024">
                                @error('version')
                                    <span class="mt-1 block text-xs font-medium text-red-600"
                                        role="alert">{{ $message }}</span>
                                @enderror
                            </label>
                            <label><span class="form-label">Número de contrato</span><input name="numero_contrato"
                                    value="{{ old('numero_contrato', $licencia->numero_contrato) }}" class="form-control"
                                    maxlength="100">
                                @error('numero_contrato')
                                    <span class="mt-1 block text-xs font-medium text-red-600"
                                        role="alert">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                        <label class="mt-4 block"><span class="form-label">Código o clave de activación</span>
                            <textarea name="clave_producto" class="form-control h-auto min-h-20 p-3 font-mono" rows="2" autocomplete="off"
                                spellcheck="false" placeholder="Solo si el producto utiliza una clave de activación">{{ old('clave_producto', $licencia->clave_producto) }}</textarea>
                            @error('clave_producto')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                            <span class="mt-1 block text-xs text-muted">
                                No captures contraseñas
                                personales. Este dato deberá protegerse como información sensible.</span>
                        </label>
                    </details>
                </section>

                <section class="form-card">
                    <h2>Observaciones</h2>
                    <label class="mt-4 block"><span class="form-label">Notas adicionales</span>
                        <textarea name="observaciones" class="form-control h-auto min-h-28 p-3" rows="4"
                            placeholder="Condiciones, restricciones o información relevante.">{{ old('observaciones', $licencia->observaciones) }}</textarea>
                        @error('observaciones')
                            <span class="mt-1 block text-xs font-medium text-red-600"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </label>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="form-card">
                    <h2>Estado</h2>
                    <label class="mt-4 block"><span class="form-label">Estado de la licencia *</span><select
                            name="estado" class="form-control" required>
                            @foreach (\App\Models\Licencia::ESTADOS as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('estado', $licencia->estado) === $valor)>{{ $etiqueta }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado')
                            <span class="mt-1 block text-xs font-medium text-red-600"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </label>
                </section>

                <section class="form-card">
                    <h2>Ubicación administrativa</h2>
                    <p class="mt-1 text-xs text-muted">Quién administra o utiliza principalmente este acceso o software.
                    </p>
                    <div class="mt-4" style="display: grid; row-gap: 1.75rem;">
                        <label class="block"><span class="form-label">Dependencia *</span><input
                                name="dependencia_id_accesos" id="licencia-dependencia" list="licencia-dependencias"
                                value="{{ old('dependencia_id_accesos', $licencia->dependencia_id_accesos) }}"
                                class="form-control" maxlength="100" autocomplete="off"
                                placeholder="Selecciona una dependencia" required><datalist id="licencia-dependencias">
                                @foreach ($dependenciasAccesos as $dependencia)
                                    <option value="{{ $dependencia['nombre'] }}"></option>
                                @endforeach
                            </datalist>
                            @error('dependencia_id_accesos')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label id="licencia-area-field" class="block" hidden><span class="form-label">Área</span><input
                                name="area_id_accesos" id="licencia-area" list="licencia-areas"
                                value="{{ old('area_id_accesos', $licencia->area_id_accesos) }}" class="form-control"
                                maxlength="100" autocomplete="off"><datalist id="licencia-areas"></datalist>
                            @error('area_id_accesos')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="block"><span class="form-label">Persona responsable</span>
                            <input id="licencia-responsable" list="licencia-responsables"
                                value="{{ $responsables->firstWhere('id', old('responsable_id', $licencia->responsable_id))?->nombre_completo }}"
                                class="form-control" autocomplete="off" placeholder="Busca por nombre">
                            <input type="hidden" name="responsable_id" id="licencia-responsable-id"
                                value="{{ old('responsable_id', $licencia->responsable_id) }}">
                            <datalist id="licencia-responsables">
                                @foreach ($responsables as $responsable)
                                    <option value="{{ $responsable->nombre_completo }}"
                                        data-id="{{ $responsable->id }}">
                                        {{ $responsable->cargo }}</option>
                                @endforeach
                            </datalist>
                            <span class="mt-1 block text-xs text-muted">Escribe para buscar entre los responsables
                                registrados.</span>
                            @error('responsable_id')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </section>

                <section class="form-card">
                    <h2>Periodo de uso</h2>
                    <div class="mt-4" style="display: grid; row-gap: 1.75rem;">
                        <label class="block"><span class="form-label">Fecha de contratación *</span><input
                                type="date" name="fecha_adquisicion"
                                value="{{ old('fecha_adquisicion', $licencia->fecha_adquisicion?->format('Y-m-d')) }}"
                                class="form-control" required>
                            @error('fecha_adquisicion')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="block"><span class="form-label">Inicio del acceso</span><input type="date"
                                name="fecha_inicio"
                                value="{{ old('fecha_inicio', $licencia->fecha_inicio?->format('Y-m-d')) }}"
                                class="form-control">
                            @error('fecha_inicio')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="block"><span class="form-label">Fecha de renovación o fin *</span><input
                                type="date" name="fecha_vencimiento"
                                value="{{ old('fecha_vencimiento', $licencia->fecha_vencimiento?->format('Y-m-d')) }}"
                                class="form-control" required>
                            @error('fecha_vencimiento')
                                <span class="mt-1 block text-xs font-medium text-red-600"
                                    role="alert">{{ $message }}</span>
                            @enderror
                        </label>
                        <input type="hidden" name="renovacion_automatica" value="0">
                        <label class="flex items-start gap-3 rounded-lg border border-line bg-surface-alt p-3"><input
                                type="checkbox" name="renovacion_automatica" value="1"
                                class="mt-0.5 size-4 rounded border-line text-brand focus:ring-brand"
                                @checked(old('renovacion_automatica', $licencia->renovacion_automatica))><span><span class="block text-sm font-semibold text-ink">Se
                                    renueva automáticamente</span><span class="mt-1 block text-xs text-muted">La
                                    suscripción continuará si no se cancela antes de esta fecha.</span></span></label>
                        @error('renovacion_automatica')
                            <span class="mt-1 block text-xs font-medium text-red-600"
                                role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </section>
            </aside>
        </div>

        <footer
            class="mt-4 flex flex-col-reverse gap-3 rounded-xl border border-line bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('licencia.index') }}"
                class="text-center text-sm font-semibold text-muted transition hover:text-ink">Cancelar</a>
            <div class="text-right">
                <button type="submit" class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white">Actualizar
                    licencia</button>
            </div>
        </footer>
    </form>

    <script type="application/json" id="licencia-departamentos-data">@json($departamentos ?? [])</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dependencia = document.getElementById('licencia-dependencia');
            const area = document.getElementById('licencia-area');
            const areaField = document.getElementById('licencia-area-field');
            const areas = document.getElementById('licencia-areas');
            const data = document.getElementById('licencia-departamentos-data');
            const responsable = document.getElementById('licencia-responsable');
            const responsableId = document.getElementById('licencia-responsable-id');
            const responsables = document.getElementById('licencia-responsables');
            if (!dependencia || !area || !areaField || !areas || !data) return;

            const departamentos = JSON.parse(data.textContent);
            const actualizarAreas = function(limpiar = false) {
                const padre = departamentos.find((item) => item.parent_id === null && item.nombre ===
                    dependencia.value.trim());
                const disponibles = padre ? departamentos.filter((item) => String(item.parent_id) === String(
                    padre.id)) : [];
                areas.replaceChildren(...disponibles.map((item) => new Option('', item.nombre)));
                areaField.hidden = disponibles.length === 0;
                if (limpiar && !disponibles.some((item) => item.nombre === area.value)) area.value = '';
            };

            dependencia.addEventListener('input', () => actualizarAreas(true));
            actualizarAreas();

            const actualizarResponsable = function() {
                if (!responsable || !responsableId || !responsables) return;
                const opcion = Array.from(responsables.options).find((item) => item.value === responsable.value
                    .trim());
                responsableId.value = opcion?.dataset.id || '';
            };

            responsable?.addEventListener('input', actualizarResponsable);
            responsable?.addEventListener('change', actualizarResponsable);
            actualizarResponsable();
        });
    </script>
@endsection
