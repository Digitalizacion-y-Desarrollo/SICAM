@extends('layouts.app')

@section('title', 'SICAM | Proveedores')

@section('breadcrumb', 'Licencias / Proveedores')

@section('content')
    <div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-heading">Módulo de Licencias</p>
                <h1 class="mt-1 text-2xl font-bold">Proveedores</h1>
                <p class="mt-1 text-sm text-muted">Catálogo de proveedores de licencias y servicios de software.</p>
            </div>
            @can('proveedores.crear')
                @include('software.proveedores.includes.modal', ['recargar' => true])
            @endcan
        </header>

        <section class="grid gap-3 sm:grid-cols-3" aria-label="Indicadores de proveedores">
            <article class="panel">
                <p class="text-xs font-semibold text-muted">Proveedores encontrados</p>
                <p class="mt-2 text-2xl font-bold text-ink">{{ number_format($proveedores->total()) }}</p>
            </article>
            <article class="panel">
                <p class="text-xs font-semibold text-muted">Activos en esta página</p>
                <p class="mt-2 text-2xl font-bold text-ink">{{ number_format($proveedores->where('activo', true)->count()) }}</p>
            </article>
            <article class="panel">
                <p class="text-xs font-semibold text-muted">Licencias relacionadas</p>
                <p class="mt-2 text-2xl font-bold text-ink">{{ number_format($proveedores->sum('licencias_count')) }}</p>
            </article>
        </section>

        <form method="GET" action="{{ route('licencia.proveedores') }}" class="form-card" aria-label="Filtros de proveedores">
            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end">
                <label>
                    <span class="form-label">Buscar proveedor</span>
                    <input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" class="form-control"
                        maxlength="191" placeholder="Nombre, razón social, RFC o contacto">
                </label>
                <label>
                    <span class="form-label">Estado</span>
                    <select name="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activos</option>
                        <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivos</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button class="action-button action-button-primary">Filtrar</button>
                    <a href="{{ route('licencia.proveedores') }}" class="action-button">Limpiar</a>
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-line bg-surface">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Directorio de proveedores</h2>
                    <p class="mt-1 text-sm text-muted">{{ $proveedores->total() }} registros encontrados.</p>
                </div>
                <a href="{{ route('licencia.index') }}" class="action-button">Ver licencias</a>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[900px]">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Datos fiscales</th>
                            <th>Contacto</th>
                            <th>Licencias</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proveedores as $proveedor)
                            <tr>
                                <td>
                                    <p class="font-semibold text-ink">{{ $proveedor->nombre }}</p>
                                    @if ($proveedor->sitio_web)
                                        <a href="{{ $proveedor->sitio_web }}" target="_blank" rel="noopener noreferrer"
                                            class="mt-1 inline-block text-xs font-semibold text-brand hover:underline">Abrir sitio web</a>
                                    @else
                                        <p class="mt-1 text-xs text-muted">Sin sitio web</p>
                                    @endif
                                </td>
                                <td>
                                    <p>{{ $proveedor->razon_social ?: 'Sin razón social' }}</p>
                                    <p class="mt-1 text-xs text-muted">RFC: {{ $proveedor->rfc ?: 'No registrado' }}</p>
                                </td>
                                <td>
                                    <p>{{ $proveedor->contacto_nombre ?: 'Sin contacto' }}</p>
                                    <p class="mt-1 text-xs text-muted">{{ $proveedor->contacto_email ?: $proveedor->contacto_telefono ?: 'Sin datos de contacto' }}</p>
                                </td>
                                <td>{{ number_format($proveedor->licencias_count) }}</td>
                                <td>
                                    <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold {{ $proveedor->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-alt text-muted' }}">
                                        {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @can('proveedores.editar')
                                        <a href="{{ route('licencia.proveedores.edit', $proveedor) }}"
                                            class="rounded-md p-1.5 text-amber-600 transition hover:bg-amber-50"
                                            title="Editar proveedor" aria-label="Editar {{ $proveedor->nombre }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931ZM19.5 7.125 16.862 4.487M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        @endcan
                                        @can('proveedores.eliminar')
                                        <form action="{{ route('licencia.proveedores.destroy', $proveedor) }}" method="POST"
                                            data-confirm-delete data-confirm-title="¿Eliminar proveedor?"
                                            data-confirm-message="¿Estás seguro de eliminar {{ $proveedor->nombre }}? Sus licencias conservarán el registro, pero quedarán sin proveedor asignado.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-md p-1.5 text-brand transition hover:bg-red-50"
                                                title="Eliminar proveedor" aria-label="Eliminar {{ $proveedor->nombre }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-5" aria-hidden="true">
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
                                <td colspan="6">
                                    <div class="py-12 text-center">
                                        <p class="text-sm font-semibold text-ink">No hay proveedores registrados</p>
                                        <p class="mt-1 text-sm text-muted">Registra el primero desde el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($proveedores->hasPages())
                <div class="border-t border-line px-5 py-4">{{ $proveedores->links() }}</div>
            @endif
        </section>
    </div>
@endsection
