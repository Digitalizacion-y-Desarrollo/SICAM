@extends('layouts.app')
@section('title', 'SICAM | Responsables')
@section('content')
    <div class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Responsables</h1>
                <p class="mt-1 text-sm text-muted">Personas que pueden tener bienes, sistemas o licencias bajo su responsabilidad</p>
            </div>
            @can('responsables.crear')
            <details class="relative" @if ($errors->any()) open @endif>
                <summary class="action-button action-button-primary list-none cursor-pointer">+ Nuevo responsable</summary>
                <form method="POST" action="{{ route('patrimonio.responsables.store') }}"
                    class="absolute right-0 z-20 mt-2 grid w-[360px] max-w-[90vw] gap-3 rounded-xl border border-line bg-white p-4 shadow-xl">
                    @csrf
                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
                            <p class="font-semibold">Revisa los campos marcados antes de guardar.</p>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <input name="numero_empleado" value="{{ old('numero_empleado') }}" class="form-control @error('numero_empleado') border-red-500 @enderror" placeholder="Número de empleado">
                    <input name="nombre" value="{{ old('nombre') }}" class="form-control @error('nombre') border-red-500 @enderror" placeholder="Nombre" required>
                    <div class="grid grid-cols-2 gap-2"><input name="apellido_paterno" class="form-control"
                            value="{{ old('apellido_paterno') }}" placeholder="Apellido paterno"><input name="apellido_materno" class="form-control"
                            value="{{ old('apellido_materno') }}" placeholder="Apellido materno"></div><input name="cargo" class="form-control"
                        value="{{ old('cargo') }}" placeholder="Cargo">
                    @include('responsables.includes.departamentos-responsable', [
                        'prefix' => 'nuevo-responsable',
                        'dependenciaValue' => old('dependencia_id_accesos'),
                        'areaValue' => old('area_id_accesos'),
                    ])
                    <input name="correo" type="email" value="{{ old('correo') }}" class="form-control @error('correo') border-red-500 @enderror"
                        placeholder="Correo"><input name="telefono" value="{{ old('telefono') }}" class="form-control" placeholder="Teléfono"><button
                        class="action-button action-button-primary justify-center">Guardar responsable</button>
                </form>
            </details>
            @endcan
        </div>
        <form method="GET" class="mt-4 rounded-xl border border-line bg-surface p-3"><label
                class="flex h-9 items-center gap-2 rounded-md border border-line bg-surface-alt px-3"><img
                    src="{{ asset('assets/icons/search.svg') }}" alt="" class="size-4"><input name="buscar"
                    value="{{ request('buscar') }}" class="w-full bg-transparent text-xs outline-none"
                    placeholder="Buscar por número de empleado, nombre, puesto..."></label>
            <div class="mt-3 flex flex-wrap gap-2"><label class="filter-button">Estado<img
                        src="{{ asset('assets/icons/chevron-down.svg') }}" alt="" class="size-3 opacity-80"><select
                        name="estado" class="bg-transparent outline-none">
                        <option value="">Todos</option>
                        <option value="activo" @selected(request('estado') === 'activo')>Activo</option>
                        <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option>
                    </select></label><button class="action-button action-button-primary h-8">Filtrar</button><a
                    href="{{ route('patrimonio.responsables') }}" class="self-center text-xs text-muted">Limpiar filtros</a>
            </div>
        </form>
        <section class="mt-4 overflow-hidden rounded-xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[1050px]">
                    <thead>
                        <tr>
                            <th>No. Empleado</th>
                            <th>Nombre</th>
                            <th>Puesto</th>
                            <th>Dependencia</th>
                            <th>Área</th>
                            <th>Bienes</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($responsables as $r)
                            <tr>
                                <td class="font-semibold text-brand">{{ $r->numero_empleado ?: '—' }}</td>
                                <td class="font-semibold">{{ $r->nombre_completo }}</td>
                                <td>{{ $r->cargo ?: '—' }}</td>
                                <td>{{ $r->dependencia_id_accesos }}</td>
                                <td>{{ $r->area_id_accesos }}</td>
                                <td><span class="rounded bg-surface-alt px-2 py-1 font-semibold">{{ $r->bienes_asignados }}
                                        activos</span></td>
                                <td><span
                                        class="asset-status {{ $r->activo ? 'asset-status-assigned' : 'asset-status-unassigned' }}">{{ $r->activo ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-3">
                                        <a data-crud class="text-brand" href="{{ route('patrimonio.responsables.show', $r) }}" title="Ver responsable">@include('patrimonio.includes.action-icon', ['icon' => 'view', 'label' => 'Ver responsable'])</a>
                                        @can('responsables.editar')
                                            <a data-crud class="text-brand" href="{{ route('patrimonio.responsables.edit', $r) }}" title="Editar responsable">@include('patrimonio.includes.action-icon', ['icon' => 'edit', 'label' => 'Editar responsable'])</a>
                                        @endcan
                                        @can('responsables.eliminar')
                                        @if (! $r->trashed())
                                            <form method="POST"
                                                action="{{ route('patrimonio.responsables.destroy', $r) }}">@csrf
                                                @method('DELETE')<button class="text-red-500" title="Eliminar responsable">@include('patrimonio.includes.action-icon', ['icon' => 'delete', 'label' => 'Eliminar responsable'])</button>
                                            </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                        </tr>@empty<tr>
                                <td colspan="8" class="py-10 text-center text-muted">No hay responsables registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-surface-alt p-4">{{ $responsables->links() }}</div>
        </section>
    </div>
@endsection
