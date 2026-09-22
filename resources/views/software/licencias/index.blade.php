@extends('layouts.app')

@section('title', 'SICAM | Licencias | Inventario')
@section('breadcrumb', 'Licencias / Inventario')

@section('content')
    @php
        $estadoClases = [
            'activa' => 'bg-emerald-50 text-emerald-700',
            'por_vencer' => 'bg-amber-50 text-amber-700',
            'vencida' => 'bg-red-50 text-red-700',
            'suspendida' => 'bg-slate-100 text-slate-700',
            'cancelada' => 'bg-slate-100 text-slate-500',
        ];
    @endphp

    <div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-heading">Módulo de Licencias</p>
                <h1 class="mt-1 text-2xl font-bold text-ink">Licencias</h1>
                <p class="mt-1 text-sm text-muted">Inventario de licencias de software institucionales.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('licencia.resumen') }}" class="action-button">Ver resumen</a>
                @can('licencias.crear')
                <a href="{{ route('licencia.create') }}" class="action-button action-button-primary">+ Registrar licencia</a>
                @endcan
            </div>
        </header>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Indicadores de licencias">
            @foreach ([['Total de licencias', $conteos->sum()], ['Activas', $conteos->get('activa', 0)], ['Por vencer', $conteos->get('por_vencer', 0)], ['Vencidas', $conteos->get('vencida', 0)]] as [$etiqueta, $total])
                <article class="metric-card">
                    <p>{{ $etiqueta }}</p><strong>{{ $total }}</strong>
                </article>
            @endforeach
        </section>

        <form method="GET" action="{{ route('licencia.index') }}" class="form-card" aria-label="Filtros de licencias">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_260px_auto] xl:items-end">
                <label><span class="form-label">Buscar</span><input type="search" name="buscar"
                        value="{{ $filtros['buscar'] ?? '' }}" class="form-control" maxlength="191"
                        placeholder="Clave, nombre, producto o fabricante"></label>
                <label><span class="form-label">Estado</span><select name="estado" class="form-control">
                        <option value="">Todos los estados</option>
                        @foreach (\App\Models\Licencia::ESTADOS as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(($filtros['estado'] ?? '') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select></label>
                <label><span class="form-label">Tipo de licencia</span><select name="tipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        @foreach (\App\Models\Licencia::TIPOS as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(($filtros['tipo'] ?? '') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select></label>
                <div class="flex gap-2"><button class="action-button action-button-primary">Filtrar</button><a
                        href="{{ route('licencia.index') }}" class="action-button">Limpiar</a></div>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-line bg-surface" aria-label="Licencias registradas">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Inventario de licencias</h2>
                    <p class="mt-1 text-sm text-muted">{{ $licencias->total() }} registros encontrados.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[1050px]">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Licencia</th>
                            <th>Tipo / modalidad</th>
                            <th>Cantidad</th>
                            <th>Proveedor</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($licencias as $licencia)
                            <tr>
                                <td class="font-semibold text-brand">{{ $licencia->clave }}</td>
                                <td>
                                    <p class="font-semibold">{{ $licencia->nombre }}</p>
                                    <p class="mt-1 text-muted">
                                        {{ $licencia->producto }}{{ $licencia->version ? ' · ' . $licencia->version : '' }}
                                    </p>
                                </td>
                                <td>{{ \App\Models\Licencia::TIPOS[$licencia->tipo_licencia] ?? $licencia->tipo_licencia }}
                                    <p class="mt-1 text-muted">
                                        {{ \App\Models\Licencia::MODALIDADES[$licencia->modalidad] ?? ($licencia->modalidad ?: 'Sin modalidad') }}
                                    </p>
                                </td>
                                <td>{{ number_format($licencia->cantidad_adquirida) }}</td>
                                <td>{{ $licencia->proveedor?->nombre ?: 'Sin proveedor' }}</td>
                                <td class="whitespace-nowrap">
                                    {{ $licencia->fecha_vencimiento?->format('d/m/Y') ?? 'Sin vencimiento' }}</td>
                                <td><span
                                        class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold {{ $estadoClases[$licencia->estado] ?? 'bg-surface-alt text-muted' }}">{{ \App\Models\Licencia::ESTADOS[$licencia->estado] ?? $licencia->estado }}</span>
                                </td>
                                <td class="flex items-center gap-2.5">
                                    @can('licencias.editar')
                                    <a href="{{ route('licencia.editar', $licencia) }}" title="Editar Licencia"
                                        class="cursor-pointer font-semibold text-yellow-600 hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>
                                    @endcan

                                    @can('licencias.eliminar')
                                    <form action="{{ route('licencia.destroy', $licencia) }}" method="POST"
                                        data-confirm-delete
                                        data-confirm-title="¿Eliminar licencia?"
                                        data-confirm-message="¿Estás seguro de eliminar {{ $licencia->nombre }}? Esta acción no se puede deshacer.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar licencia"
                                            class="cursor-pointer font-semibold text-brand hover:underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="py-12 text-center"><img src="{{ asset('assets/icons/file-key.svg') }}"
                                            alt="" class="mx-auto size-10 opacity-60">
                                        <p class="mt-3 text-sm font-semibold text-ink">No hay licencias registradas</p>
                                        <p class="mt-1 text-sm text-muted">Las licencias aparecerán aquí cuando se habilite
                                            su registro.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($licencias->hasPages())
                <div class="border-t border-line px-5 py-4">{{ $licencias->links() }}</div>
            @endif
        </section>
    </div>
@endsection
