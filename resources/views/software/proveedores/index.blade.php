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
            @include('software.proveedores.includes.modal', ['recargar' => true])
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
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
