@extends('layouts.app')
@section('title', 'SICAM | Bienes patrimoniales')
@section('content')
    @php
        $filtrosActivos = collect(request()->only(['buscar', 'categoria_id', 'estado', 'dependencia', 'area', 'responsable_id']))->filter(fn ($valor) => filled($valor));
        $hayFiltros = $filtrosActivos->isNotEmpty();
    @endphp
    <div class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Bienes Patrimoniales</h1>
                <p class="mt-1 text-sm text-muted">Consulta los bienes patrimoniales registrados</p>
            </div>
            @can('bienes.crear')
            <a data-crud href="{{ route('patrimonio.bienes.create') }}" class="action-button action-button-primary">+
                Registrar bien</a>
            @endcan
        </div>
        <details class="group mt-4 overflow-hidden rounded-xl border border-line bg-surface" @if($hayFiltros) open @endif>
            <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3 text-sm font-semibold text-ink">
                <img src="{{ asset('assets/icons/search.svg') }}" alt="" class="size-4">
                <span>Filtros de búsqueda</span>
                @if ($hayFiltros)
                    <span class="rounded-full bg-brand/[.08] px-2 py-0.5 text-xs text-brand">{{ $filtrosActivos->count() }} {{ $filtrosActivos->count() === 1 ? 'activo' : 'activos' }}</span>
                @endif
                <span class="ml-auto text-xs font-normal text-muted">{{ $bienes->total() }} {{ $bienes->total() === 1 ? 'resultado' : 'resultados' }}</span>
                <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt="" class="size-4 transition-transform group-open:rotate-180">
            </summary>
        <form method="GET" class="border-t border-line p-4" aria-label="Filtros de bienes patrimoniales">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <label class="sm:col-span-2 lg:col-span-3 xl:col-span-2"><span class="form-label">Buscar</span><input name="buscar"
                        value="{{ request('buscar') }}" class="form-control"
                        placeholder="Folio, patrimonial, serie o nombre"></label>
                <label><span class="form-label">Categoría</span><select name="categoria_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected((string) request('categoria_id') === (string) $categoria->id)>{{ $categoria->padre ? $categoria->padre->nombre.' / ' : '' }}{{ $categoria->nombre }}</option>
                    @endforeach
                </select></label>
                <label><span class="form-label">Estado</span><select name="estado" class="form-control">
                    <option value="">Todos</option>
                    @foreach (['DISPONIBLE' => 'Disponible', 'ASIGNADO' => 'Asignado', 'EN_RESGUARDO' => 'En resguardo'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
                <label><span class="form-label">Dependencia</span><select name="dependencia" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($dependencias as $dependencia)
                        <option value="{{ $dependencia }}" @selected(request('dependencia') === $dependencia)>{{ $dependencia }}</option>
                    @endforeach
                </select></label>
                <label><span class="form-label">Área</span><select name="area" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area }}" @selected(request('area') === $area)>{{ $area }}</option>
                    @endforeach
                </select></label>
                <label class="sm:col-span-2 lg:col-span-3 xl:col-span-2"><span class="form-label">Responsable</span><select name="responsable_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($responsables as $responsable)
                        <option value="{{ $responsable->id }}" @selected((string) request('responsable_id') === (string) $responsable->id)>{{ $responsable->nombre_completo }}</option>
                    @endforeach
                </select></label>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button class="action-button action-button-primary">Aplicar filtros</button>
                @if ($hayFiltros)
                    <a class="action-button" href="{{ route('patrimonio.bienes') }}">Limpiar filtros</a>
                @endif
                <span class="ml-auto text-xs text-muted">{{ $bienes->total() }} {{ $bienes->total() === 1 ? 'bien encontrado' : 'bienes encontrados' }}</span>
            </div>
        </form>
        </details>
        <section class="mt-4 overflow-hidden rounded-xl border border-line bg-surface">
            <div class="overflow-x-auto">
                <table class="asset-table w-full min-w-[1160px]">
                    <thead>
                        <tr>
                            <th>Folio SICAM</th>
                            <th>Bien / Activo</th>
                            <th>Categoría</th>
                            <th>No. Patrimonial</th>
                            <th>Dependencia/Área</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bienes as $bien)
                            <tr>
                                <td class="font-semibold text-brand">{{ $bien->folio_sicam }}</td>
                                <td>
                                    <p class="font-semibold">{{ $bien->nombre }}</p>
                                    <p class="text-xs text-muted">
                                        {{ collect([$bien->marca, $bien->modelo])->filter()->implode(' · ') }}</p>
                                </td>
                                <td>{{ $bien->categoria?->nombre ?? 'Categoría eliminada' }}</td>
                                <td>{{ $bien->numero_patrimonial }}</td>
                                <td>{{ $bien->dependencia_id_accesos }} / {{ $bien->area_id_accesos }}</td>
                                <td>{{ $bien->asignacionActual?->responsable?->nombre_completo ?? ($bien->asignacionActual?->tipo_responsabilidad === 'AREA' ? 'Área: ' . $bien->area_id_accesos : 'Sin asignar') }}
                                </td>
                                <td><span
                                        class="asset-status asset-status-{{ $bien->estado === 'DISPONIBLE' ? 'available' : 'assigned' }}">{{ str_replace('_', ' ', ucfirst(strtolower($bien->estado))) }}</span>
                                </td>
                                <td>{{ $bien->updated_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="flex gap-2"><a data-crud class="text-brand" title="Ver Activo"
                                            href="{{ route('patrimonio.bienes.show', $bien) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>

                                        </a>
                                        @can('bienes.editar')
                                        <a data-crud class="text-brand" title="Editar Activo"
                                            href="{{ route('patrimonio.bienes.edit', $bien) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>

                                        </a>
                                        @endcan
                                        <a data-download data-filename="{{ $bien->folio_sicam }}.png" class="text-brand" title="Descargar QR"
                                            href="{{ route('patrimonio.bienes.qr', ['bien' => $bien, 'formato' => 'png']) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                            </svg>

                                        </a>
                                        @can('bienes.eliminar')
                                        <form method="POST" action="{{ route('patrimonio.bienes.destroy', $bien) }}">@csrf
                                            @method('DELETE')<button class="text-red-700" title="Eliminar Activo">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-muted">
                                    {{ $hayFiltros ? 'No hay bienes que coincidan con los filtros.' : 'Aún no hay bienes registrados.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-line p-4 text-sm text-muted">{{ $bienes->links() }}</div>
        </section>
    </div>
@endsection
