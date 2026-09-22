@extends('layouts.app')

@section('title', 'SICAM | Licencias | Resumen')
@section('breadcrumb', 'Licencias / Resumen')

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
            <div><p class="section-heading">Módulo de Licencias</p><h1 class="mt-1 text-2xl font-bold text-ink">Resumen</h1><p class="mt-1 text-sm text-muted">Panorama general del licenciamiento institucional y sus vencimientos.</p></div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('licencia.index') }}" class="action-button">Ver licencias</a>@can('proveedores.ver')<a href="{{ route('licencia.proveedores') }}" class="action-button">Ver proveedores</a>@endcan
@can('licencias.crear')<a href="{{ route('licencia.create') }}" class="action-button action-button-primary">+ Registrar licencia</a>@endcan</div>
        </header>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Indicadores de licencias">
            @foreach ([
                ['Licencias registradas', $metricas['total'], 'Registros en el inventario'],
                ['Activas', $metricas['activas'], 'Disponibles para uso'],
                ['Por vencer', $metricas['por_vencer'], 'Requieren seguimiento'],
                ['Vencidas', $metricas['vencidas'], 'Requieren regularización'],
            ] as [$etiqueta, $total, $detalle])
                <article class="metric-card"><p>{{ $etiqueta }}</p><strong>{{ $total }}</strong><span class="mt-1 block text-xs text-muted">{{ $detalle }}</span></article>
            @endforeach
        </section>

        @if (! $metricas['total'])
            <section class="panel text-center"><img src="{{ asset('assets/icons/file-key.svg') }}" alt="" class="mx-auto size-10 opacity-60"><h2 class="mt-3">Aún no hay licencias registradas</h2><p class="mx-auto mt-2 max-w-md text-sm text-muted">Cuando se registren licencias podrás consultar aquí su estado, distribución y próximos vencimientos.</p><a href="{{ route('licencia.index') }}" class="action-button mt-4">Ir al inventario</a></section>
        @else
            <div class="grid gap-5 xl:grid-cols-5">
                <section class="panel xl:col-span-3" aria-labelledby="estados-licencia-title">
                    <div class="flex items-center justify-between gap-3"><div><h2 id="estados-licencia-title">Estado del licenciamiento</h2><p class="mt-1 text-sm text-muted">Distribución de las licencias registradas.</p></div><span class="text-xs text-muted">{{ number_format($metricas['cantidad_adquirida']) }} unidades adquiridas</span></div>
                    <div class="mt-5 space-y-4">
                        @foreach ($estadoResumen as $estado)
                            <div><div class="flex justify-between gap-3 text-xs"><span class="font-medium text-ink">{{ $estado['etiqueta'] }}</span><span class="text-muted">{{ $estado['total'] }} · {{ $estado['porcentaje'] }}%</span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-alt"><div class="h-full rounded-full bg-brand" style="width: {{ $estado['porcentaje'] }}%"></div></div></div>
                        @endforeach
                    </div>
                </section>
                <section class="panel xl:col-span-2" aria-labelledby="tipos-licencia-title">
                    <h2 id="tipos-licencia-title">Tipos más utilizados</h2><p class="mt-1 text-sm text-muted">Principales esquemas de licenciamiento.</p>
                    <ol class="mt-4 divide-y divide-line">
                        @forelse ($porTipo as $tipo)
                            <li class="flex items-center justify-between gap-4 py-3 first:pt-0"><span class="text-sm font-medium text-ink">{{ \App\Models\Licencia::TIPOS[$tipo->tipo_licencia] ?? $tipo->tipo_licencia }}</span><span class="rounded-md bg-brand/[.07] px-2 py-1 text-xs font-semibold text-brand">{{ $tipo->total }}</span></li>
                        @empty
                            <li class="py-3 text-sm text-muted">Sin información disponible.</li>
                        @endforelse
                    </ol>
                </section>
            </div>

            <section class="panel" aria-labelledby="vencimientos-title">
                <div class="flex flex-wrap items-end justify-between gap-3"><div><h2 id="vencimientos-title">Próximos vencimientos</h2><p class="mt-1 text-sm text-muted">Licencias vigentes ordenadas por fecha de vencimiento.</p></div><a href="{{ route('licencia.index', ['estado' => 'por_vencer']) }}" class="text-xs font-semibold text-brand hover:underline">Ver por vencer</a></div>
                <div class="mt-4 overflow-x-auto"><table class="data-table w-full min-w-[720px]">
                        <thead><tr><th>Licencia</th><th>Producto</th><th>Proveedor</th><th>Estado</th><th>Vencimiento</th></tr></thead>
                        <tbody>
                            @forelse ($proximosVencimientos as $licencia)
                                <tr><td><span class="font-semibold text-ink">{{ $licencia->nombre }}</span><p class="mt-1 text-muted">{{ $licencia->clave }}</p></td><td>{{ $licencia->producto }}</td><td>{{ $licencia->proveedor?->nombre ?: 'Sin proveedor' }}</td><td><span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold {{ $estadoClases[$licencia->estado] ?? 'bg-surface-alt text-muted' }}">{{ \App\Models\Licencia::ESTADOS[$licencia->estado] ?? $licencia->estado }}</span></td><td class="whitespace-nowrap">{{ $licencia->fecha_vencimiento?->format('d/m/Y') }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-muted">No hay vencimientos próximos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table></div>
            </section>
        @endif
    </div>
@endsection
