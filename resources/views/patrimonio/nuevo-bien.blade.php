@extends('layouts.app')
@section('title', isset($bien) ? 'SICAM | Editar bien' : 'SICAM | Registrar bien')
@section('content')
@php($fieldValue = fn ($key, $default = null) => old($key, data_get($defaults ?? [], $key, $default)))
@php($dependenciasAccesos = collect($departamentos)->whereNull('parent_id'))
<form id="registro-bien" class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-7" action="{{ isset($bien) ? route('patrimonio.bienes.update', $bien) : route('patrimonio.bienes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @isset($bien) @method('PUT') @endisset
    <div class="mb-4"><h1 class="text-2xl font-bold">{{ isset($bien) ? "Editar bien" : "Registrar bien" }}</h1><p class="mt-1 text-sm text-muted">{{ isset($bien) ? 'Actualiza los datos del bien patrimonial' : 'Agrega un bien al inventario patrimonial' }}</p></div>
    @if ($categorias->isEmpty())
        <p role="status" class="mb-4 rounded-lg bg-warm p-4 text-sm">Primero <a class="font-semibold text-brand underline" href="{{ route('patrimonio.categorias') }}">registra una categoría activa</a> para dar de alta un bien.</p>
    @endif
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-4">
            <section class="form-card">
                <h2>Información general</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label><span class="form-label">Categoría *</span><select name="categoria_id" id="bien-categoria" data-tour="Elige la categoría para cargar sus características. Los campos marcados con * son obligatorios." data-tour-title="Categoría" class="form-control" required>
                        <option value="">Selecciona una categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected((string) $fieldValue('categoria_id') === (string) $categoria->id)>{{ $categoria->padre ? $categoria->padre->nombre.' / ' : '' }}{{ $categoria->nombre }}</option>
                        @endforeach
                    </select></label>
                    @include('patrimonio.includes.bien-input', ['name' => 'nombre', 'label' => 'Nombre o descripción', 'required' => true, 'max' => 180])
                    <label><span class="form-label">No. patrimonial</span><input class="form-control bg-surface-alt text-muted" value="{{ $bien->numero_patrimonial ?? 'Se genera al guardar' }}" readonly><span class="mt-1 block text-[11px] text-muted">El folio SICAM y el número patrimonial son automáticos.</span></label>
                    @include('patrimonio.includes.bien-input', ['name' => 'numero_serie', 'label' => 'No. de serie', 'max' => 120])
                    @include('patrimonio.includes.bien-input', ['name' => 'marca', 'label' => 'Marca', 'max' => 100])
                    @include('patrimonio.includes.bien-input', ['name' => 'modelo', 'label' => 'Modelo', 'max' => 100])
                </div>
                <label class="mt-4 block rounded-lg border border-dashed border-line p-4">
                    <span class="form-label">Fotografía</span><span class="mb-3 block text-xs text-muted">Selecciona una imagen PNG o JPG de hasta 5 MB.</span>
                    <input name="fotografia" id="bien-fotografia" type="file" accept="image/png,image/jpeg" class="block w-full min-w-0 text-xs">
                </label>
                <p id="foto-error" class="mt-2 text-sm text-red-700" role="alert"></p>
                <img id="foto-preview" alt="Vista previa de la fotografía del bien" class="mt-3 hidden max-h-48 rounded-lg object-contain">
            </section>
            <section class="form-card">
                <h2>Características del activo</h2>
                <p id="campos-vacios" class="mt-3 text-sm text-muted" role="status">Selecciona una categoría para ver sus características.</p>
                @foreach ($categorias as $categoria)
                    <fieldset data-campos-categoria="{{ $categoria->id }}" class="mt-4 grid gap-4 sm:grid-cols-2" @disabled((string) $fieldValue('categoria_id') !== (string) $categoria->id) @if((string) $fieldValue('categoria_id') !== (string) $categoria->id) hidden @endif>
                        @foreach ($categoria->campos as $campo)
                            <label><span class="form-label">{{ $campo->nombre }}{{ $campo->requerido ? ' *' : '' }}</span>
                                @if ($campo->tipo === 'SELECCION')
                                    <select name="campos[{{ $campo->id }}]" class="form-control" @required($campo->requerido)>
                                        <option value="">Selecciona una opción</option>
                                        @foreach ($campo->opciones ?? [] as $opcion)
                                            <option value="{{ $opcion }}" @selected((string) $fieldValue('campos.'.$campo->id) === (string) $opcion)>{{ $opcion }}</option>
                                        @endforeach
                                    </select>
                                    @if(empty($campo->opciones))<span class="mt-1 block text-xs text-muted">Esta característica todavía no tiene opciones configuradas.</span>@endif
                                @else
                                    <input name="campos[{{ $campo->id }}]" value="{{ $fieldValue('campos.'.$campo->id) }}" class="form-control" type="{{ match ($campo->tipo) { 'NUMERO' => 'number', 'FECHA' => 'date', default => 'text' } }}" @if($campo->tipo === 'NUMERO') step="any" @endif @required($campo->requerido)>
                                @endif
                            </label>
                        @endforeach
                    </fieldset>
                @endforeach
                <noscript><p class="mt-3 text-sm text-red-700">Activa JavaScript para seleccionar las características y la responsabilidad del bien.</p></noscript>
            </section>
            <section class="form-card">
                <h2>Responsabilidad del bien</h2>
                <fieldset class="mt-4 flex flex-wrap gap-5 text-sm">
                    <legend class="sr-only">Tipo de responsabilidad</legend>
                    @foreach (['persona' => 'Persona', 'area' => 'Área', 'ninguna' => 'Sin asignar'] as $value => $label)
                        <label><input type="radio" name="responsabilidad" value="{{ $value }}" @checked($fieldValue('responsabilidad', 'ninguna') === $value)> {{ $label }}</label>
                    @endforeach
                </fieldset>
                <fieldset id="bien-persona" class="mt-4 space-y-4" @disabled($fieldValue('responsabilidad', 'ninguna') !== 'persona') @if($fieldValue('responsabilidad', 'ninguna') !== 'persona') hidden @endif>
                    <label class="block text-sm"><input id="bien-crear-responsable" type="checkbox" name="crear_responsable" value="1" @checked($fieldValue('crear_responsable'))> Registrar un nuevo responsable</label>
                    <label id="bien-responsable-existente" class="block"><span class="form-label">Responsable *</span>
                        <select name="responsable_id" id="bien-responsable" class="form-control">
                            <option value="">Selecciona un responsable</option>
                            @foreach ($responsables as $responsable)
                                <option value="{{ $responsable->id }}" data-dependencia="{{ $responsable->dependencia_id_accesos }}" data-area="{{ $responsable->area_id_accesos }}" @selected((string) $fieldValue('responsable_id') === (string) $responsable->id)>{{ $responsable->nombre_completo }}{{ $responsable->numero_empleado ? ' · '.$responsable->numero_empleado : '' }}</option>
                            @endforeach
                        </select>
                        <span id="responsable-ayuda" class="mt-2 block text-xs text-muted" role="status">Solo se muestran responsables activos de la ubicación indicada.</span>
                    </label>
                    <fieldset id="bien-nuevo-responsable" class="grid gap-3 rounded-lg border border-line p-4 sm:grid-cols-2" disabled hidden>
                        @foreach (['nombre' => 'Nombre', 'apellido_paterno' => 'Apellido paterno', 'apellido_materno' => 'Apellido materno', 'numero_empleado' => 'No. de empleado', 'cargo' => 'Cargo', 'correo' => 'Correo', 'telefono' => 'Teléfono'] as $key => $label)
                            @include('patrimonio.includes.bien-input', ['name' => 'nuevo_responsable['.$key.']', 'oldKey' => 'nuevo_responsable.'.$key, 'label' => $label, 'required' => $key === 'nombre', 'type' => $key === 'correo' ? 'email' : 'text'])
                        @endforeach
                        <p class="text-xs text-muted sm:col-span-2">Se registrará en la dependencia y el área del bien al guardar.</p>
                    </fieldset>
                </fieldset>
                <p id="bien-area" class="mt-4 text-sm text-muted" @if($fieldValue('responsabilidad') !== 'area') hidden @endif>El resguardo quedará a cargo del área indicada en Ubicación administrativa.</p>
            </section>
        </div>
        <aside class="space-y-4">
            <section class="form-card"><h2>Ubicación administrativa</h2><div class="mt-4 space-y-4">
                <label class="block"><span class="form-label">Dependencia *</span>
                    <input name="dependencia_id_accesos" id="bien-dependencia" list="dependencias-accesos" value="{{ $fieldValue('dependencia_id_accesos') }}" class="form-control" maxlength="100" autocomplete="off" required>
                    <datalist id="dependencias-accesos">
                        @foreach ($dependenciasAccesos as $dependencia)
                            <option value="{{ $dependencia['nombre'] }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="block"><span class="form-label">Área *</span>
                    <input name="area_id_accesos" id="bien-area-accesos" list="areas-accesos" value="{{ $fieldValue('area_id_accesos') }}" class="form-control" maxlength="100" autocomplete="off">
                    <datalist id="areas-accesos"></datalist>
                    <span id="areas-accesos-ayuda" class="mt-1 block text-xs text-muted">Selecciona una dependencia para consultar sus áreas.</span>
                </label>
                @include('patrimonio.includes.bien-input', ['name' => 'ubicacion_fisica', 'label' => 'Ubicación física', 'max' => 191])
            </div></section>
            <section class="form-card"><h2>Estado inicial</h2>
                <label class="mt-4 block"><span class="form-label">Estado *</span><select name="estado" id="bien-estado" class="form-control" required>
                    @foreach (['DISPONIBLE' => 'Disponible', 'ASIGNADO' => 'Asignado', 'EN_RESGUARDO' => 'En resguardo'] as $value => $label)
                        <option value="{{ $value }}" @selected($fieldValue('estado', 'DISPONIBLE') === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
                <p class="mt-3 text-xs text-muted">Sin asignar: disponible. Con persona o área: asignado o en resguardo.</p>
            </section>
            <section class="form-card"><h2>Información adicional</h2><div class="mt-4 space-y-4">
                @include('patrimonio.includes.bien-input', ['name' => 'fecha_adquisicion', 'label' => 'Fecha de adquisición', 'type' => 'date'])
                @include('patrimonio.includes.bien-input', ['name' => 'costo', 'label' => 'Costo de adquisición', 'type' => 'number', 'step' => '0.01', 'min' => 0])
                @include('patrimonio.includes.bien-input', ['name' => 'proveedor', 'label' => 'Proveedor', 'max' => 150])
                @include('patrimonio.includes.bien-input', ['name' => 'numero_factura', 'label' => 'No. de factura', 'max' => 100])
                <label class="block"><span class="form-label">Observaciones</span><textarea name="observaciones" class="form-control" rows="3" maxlength="10000">{{ $fieldValue('observaciones') }}</textarea></label>
            </div></section>
        </aside>
    </div>
    <footer class="mt-4 flex items-center justify-between rounded-xl border border-line bg-surface p-4">
        <a href="{{ route('patrimonio.bienes') }}" class="text-sm font-semibold text-muted">Cancelar</a>
        <button type="submit" class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white disabled:opacity-50" @disabled($categorias->isEmpty())>{{ isset($bien) ? 'Guardar cambios' : 'Guardar bien' }}</button>
    </footer>
</form>
<script type="application/json" id="departamentos-accesos-data">@json($departamentos)</script>
@endsection
