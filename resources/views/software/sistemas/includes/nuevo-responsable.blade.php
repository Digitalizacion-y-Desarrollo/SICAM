<div class="mt-3" data-nuevo-responsable="{{ $rol }}" data-url="{{ route('patrimonio.responsables.store') }}">
    <button type="button" class="text-sm font-semibold text-brand hover:underline" data-responsable-toggle aria-haspopup="dialog" aria-controls="nuevo-responsable-{{ $rol }}">+ Registrar un nuevo responsable {{ $rol === 'funcional' ? 'funcional' : 'técnico' }}</button>
    <dialog id="nuevo-responsable-{{ $rol }}" class="responsable-modal" aria-labelledby="titulo-responsable-{{ $rol }}" data-responsable-modal>
        <div class="flex items-center justify-between gap-4 border-b border-line p-5">
            <h2 id="titulo-responsable-{{ $rol }}" class="text-lg font-bold">Registrar responsable {{ $rol === 'funcional' ? 'funcional' : 'técnico' }}</h2>
            <button type="button" class="action-button" data-responsable-cerrar aria-label="Cerrar registro de responsable">X</button>
        </div>
    <fieldset class="grid gap-4 p-5 sm:grid-cols-2" data-responsable-campos disabled>
        <p class="text-xs text-muted">Se guardará en el catálogo con la dependencia y el área indicadas en este formulario, aunque después canceles el registro del sistema.</p>
        @foreach (['nombre' => ['Nombre *', 100], 'apellido_paterno' => ['Apellido paterno', 100], 'apellido_materno' => ['Apellido materno', 100], 'numero_empleado' => ['No. de empleado', 50], 'cargo' => ['Cargo', 150], 'correo' => ['Correo', 150], 'telefono' => ['Teléfono', 30]] as $campo => [$etiqueta, $limite])
            <label class="block"><span class="form-label">{{ $etiqueta }}</span><input type="{{ $campo === 'correo' ? 'email' : 'text' }}" class="form-control" data-responsable-campo="{{ $campo }}" maxlength="{{ $limite }}" autocomplete="off"></label>
        @endforeach
        <label class="block"><span class="form-label">Dependencia *</span>
            <select class="form-control" data-responsable-campo="dependencia_id_accesos" data-responsable-dependencia>
                <option value="">Selecciona una dependencia</option>
                @foreach (collect($departamentos ?? [])->whereNull('parent_id') as $dependencia)
                    <option value="{{ $dependencia['nombre'] }}" data-departamento-id="{{ $dependencia['id'] }}">{{ $dependencia['nombre'] }}</option>
                @endforeach
            </select>
        </label>
        <label class="block"><span class="form-label">Área</span>
            <select class="form-control" data-responsable-campo="area_id_accesos" data-responsable-area disabled><option value="">Selecciona primero una dependencia</option></select>
        </label>
        @if (empty($departamentos))<p class="text-sm text-amber-800 sm:col-span-2" role="status">No se pudo cargar el catálogo de dependencias. Recarga la página para intentarlo nuevamente.</p>@endif
        <script type="application/json" data-responsable-departamentos>@json($departamentos ?? [])</script>
        <p class="text-sm text-red-700 sm:col-span-2" role="alert" data-responsable-error hidden></p>
        <div class="flex flex-wrap justify-end gap-2 sm:col-span-2"><button type="button" class="action-button action-button-primary" data-responsable-guardar>Guardar responsable</button><button type="button" class="action-button" data-responsable-cancelar>Cancelar</button></div>
    </fieldset>
    </dialog>
    <p class="mt-2 text-xs text-muted" role="status" data-responsable-resultado></p>
</div>
