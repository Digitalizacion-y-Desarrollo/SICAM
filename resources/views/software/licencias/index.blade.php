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
                <a href="{{ route('licencia.create') }}" class="action-button action-button-primary">+ Registrar licencia</a>
            </div>
        </header>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Indicadores de licencias">
            @foreach ([
                ['Total de licencias', $conteos->sum()],
                ['Activas', $conteos->get('activa', 0)],
                ['Por vencer', $conteos->get('por_vencer', 0)],
                ['Vencidas', $conteos->get('vencida', 0)],
            ] as [$etiqueta, $total])
                <article class="metric-card"><p>{{ $etiqueta }}</p><strong>{{ $total }}</strong></article>
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
                <div><h2 class="text-base font-semibold text-ink">Inventario de licencias</h2><p class="mt-1 text-sm text-muted">{{ $licencias->total() }} registros encontrados.</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-[1050px]">
                    <thead><tr><th>Clave</th><th>Licencia</th><th>Tipo / modalidad</th><th>Cantidad</th><th>Proveedor</th><th>Vencimiento</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($licencias as $licencia)
                            <tr>
                                <td class="font-semibold text-brand">{{ $licencia->clave }}</td>
                                <td><p class="font-semibold">{{ $licencia->nombre }}</p><p class="mt-1 text-muted">{{ $licencia->producto }}{{ $licencia->version ? ' · '.$licencia->version : '' }}</p></td>
                                <td>{{ \App\Models\Licencia::TIPOS[$licencia->tipo_licencia] ?? $licencia->tipo_licencia }}<p class="mt-1 text-muted">{{ \App\Models\Licencia::MODALIDADES[$licencia->modalidad] ?? ($licencia->modalidad ?: 'Sin modalidad') }}</p></td>
                                <td>{{ number_format($licencia->cantidad_adquirida) }}</td>
                                <td>{{ $licencia->proveedor?->nombre ?: 'Sin proveedor' }}</td>
                                <td class="whitespace-nowrap">{{ $licencia->fecha_vencimiento?->format('d/m/Y') ?? 'Sin vencimiento' }}</td>
                                <td><span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold {{ $estadoClases[$licencia->estado] ?? 'bg-surface-alt text-muted' }}">{{ \App\Models\Licencia::ESTADOS[$licencia->estado] ?? $licencia->estado }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="py-12 text-center"><img src="{{ asset('assets/icons/file-key.svg') }}" alt="" class="mx-auto size-10 opacity-60"><p class="mt-3 text-sm font-semibold text-ink">No hay licencias registradas</p><p class="mt-1 text-sm text-muted">Las licencias aparecerán aquí cuando se habilite su registro.</p></div></td></tr>
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
