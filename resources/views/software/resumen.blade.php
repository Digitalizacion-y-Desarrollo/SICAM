@extends('layouts.app')

@section('title', 'SICAM | Software | Resumen')
@section('breadcrumb', 'Software / Resumen')

@section('content')
    @php
        $estadoClases = [
            'produccion' => 'bg-emerald-50 text-emerald-700',
            'desarrollo' => 'bg-blue-50 text-blue-700',
            'pruebas' => 'bg-violet-50 text-violet-700',
            'mantenimiento' => 'bg-amber-50 text-amber-700',
            'suspendido' => 'bg-red-50 text-red-700',
            'descontinuado' => 'bg-slate-100 text-slate-600',
        ];
    @endphp

    <div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-heading">Módulo de Software</p>
                <h1 class="mt-1 text-2xl font-bold text-ink">Resumen</h1>
                <p class="mt-1 text-sm text-muted">Seguimiento del inventario de sistemas, aplicaciones y servicios institucionales.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('sistemas.ver')
                <a href="{{ route('software.sistemas') }}" class="action-button">Ver sistemas</a>
                @endcan
                @can('sistemas.crear')
                <a href="{{ route('software.sistemas.create') }}" class="action-button action-button-primary" data-crud>+ Registrar sistema</a>
                @endcan
            </div>
        </header>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Indicadores de Software">
            @foreach ([
                ['Sistemas registrados', $metricas['total'], 'Inventario total'],
                ['En producción', $metricas['produccion'], 'Disponibles para operación'],
                ['En proceso', $metricas['en_proceso'], 'Desarrollo y pruebas'],
                ['Atención requerida', $metricas['sin_responsable'], 'Sin responsable asignado'],
            ] as [$etiqueta, $total, $detalle])
                <article class="metric-card">
                    <p>{{ $etiqueta }}</p>
                    <strong>{{ $total }}</strong>
                    <span class="mt-1 block text-xs text-muted">{{ $detalle }}</span>
                </article>
            @endforeach
        </section>

        @if (! $metricas['total'])
            <section class="panel text-center">
                <img src="{{ asset('assets/icons/terminal-square.svg') }}" alt="" class="mx-auto size-10 opacity-70">
                <h2 class="mt-3">Aún no hay sistemas registrados</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-muted">Registra el primer sistema para visualizar sus estados, responsables y distribución por dependencia.</p>
                @can('sistemas.crear')
                    <a href="{{ route('software.sistemas.create') }}" class="action-button action-button-primary mt-4" data-crud>Registrar sistema</a>
                @endcan
            </section>
        @else
            <div class="grid gap-5 xl:grid-cols-5">
                <section class="panel xl:col-span-3" aria-labelledby="estados-title">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 id="estados-title">Estado del inventario</h2>
                            <p class="mt-1 text-sm text-muted">Distribución actual de los sistemas registrados.</p>
                        </div>
                        <a href="{{ route('software.sistemas') }}" class="text-xs font-semibold text-brand hover:underline">Ver detalle</a>
                    </div>
                    <div class="mt-5 space-y-4">
                        @foreach ($estadoResumen as $estado)
                            <div>
                                <div class="flex justify-between gap-3 text-xs">
                                    <span class="font-medium text-ink">{{ $estado['etiqueta'] }}</span>
                                    <span class="text-muted">{{ $estado['total'] }} · {{ $estado['porcentaje'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-alt">
                                    <div class="h-full rounded-full bg-brand" style="width: {{ $estado['porcentaje'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="panel xl:col-span-2" aria-labelledby="dependencias-title">
                    <h2 id="dependencias-title">Dependencias con más sistemas</h2>
                    <p class="mt-1 text-sm text-muted">Top 5 por registros activos.</p>
                    <ol class="mt-4 divide-y divide-line">
                        @foreach ($porDependencia as $dependencia)
                            <li class="flex items-center justify-between gap-4 py-3 first:pt-0">
                                <span class="text-sm font-medium text-ink">{{ $dependencia->dependencia_id_accesos }}</span>
                                <span class="rounded-md bg-brand/[.07] px-2 py-1 text-xs font-semibold text-brand">{{ $dependencia->total }}</span>
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>

            <section class="panel" aria-labelledby="recientes-title">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="recientes-title">Actualizaciones recientes</h2>
                        <p class="mt-1 text-sm text-muted">Los últimos sistemas modificados en el inventario.</p>
                    </div>
                    <a href="{{ route('software.sistemas') }}" class="text-xs font-semibold text-brand hover:underline">Ir al inventario</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="data-table w-full min-w-[720px]">
                        <thead>
                            <tr>
                                <th>Sistema</th>
                                <th>Dependencia</th>
                                <th>Responsables</th>
                                <th>Estado</th>
                                <th>Actualización</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recientes as $sistema)
                                <tr>
                                    <td><a href="{{ route('software.sistemas.show', $sistema) }}" class="font-semibold text-brand hover:underline">{{ $sistema->nombre }}</a><p class="mt-1 text-muted">{{ $sistema->clave }}</p></td>
                                    <td>{{ $sistema->dependencia_id_accesos }}<p class="mt-1 text-muted">{{ $sistema->area_id_accesos ?: 'Sin área asignada' }}</p></td>
                                    <td>F: {{ $sistema->responsableFuncional?->nombre_completo ?: 'Sin asignar' }}<p class="mt-1 text-muted">T: {{ $sistema->responsableTecnico?->nombre_completo ?: 'Sin asignar' }}</p></td>
                                    <td><span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold {{ $estadoClases[$sistema->estado] ?? 'bg-surface-alt text-muted' }}">{{ \App\Models\Sistema::ESTADOS[$sistema->estado] ?? $sistema->estado }}</span></td>
                                    <td class="whitespace-nowrap">{{ $sistema->updated_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
