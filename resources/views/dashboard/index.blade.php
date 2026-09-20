@extends('layouts.app')

@section('title', 'SICAM | Inicio')

@section('content')
    <div class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-8">
        <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-[26px]">Inicio</h1>
                <p class="mt-1 text-xs text-muted sm:text-sm">Visión general del control de activos municipales</p>
            </div>
            <p class="flex items-center gap-2 text-[11px] text-muted">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                Actualizado: {{ $actualizadoEn->format('d/m/Y H:i') }}
            </p>
        </header>

        <section>
            <h2 class="section-heading">Módulos del sistema</h2>
            <div class="mt-2.5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <article class="module-card module-card-active">
                    <div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/building.svg') }}" alt="" class="size-6"></span></div>
                    <div><h3>Patrimonio</h3><p>{{ number_format($conteos['bienes']) }} bienes</p></div>
                    <a href="{{ route('patrimonio.resumen') }}" class="module-action">Ver módulo →</a>
                </article>
                <article class="module-card module-card-active">
                    <div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/terminal.svg') }}" alt="" class="size-6"></span></div>
                    <div><h3>Software</h3><p>{{ number_format($conteos['sistemas']) }} sistemas</p></div>
                    <a href="{{ route('software.resumen') }}" class="module-action">Ver módulo →</a>
                </article>
                <article class="module-card module-card-active">
                    <div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/file-check.svg') }}" alt="" class="size-6"></span></div>
                    <div><h3>Licencias</h3><p>{{ number_format($conteos['licencias']) }} registros · {{ number_format($conteos['unidades_licencia']) }} accesos</p></div>
                    <a href="{{ route('licencia.resumen') }}" class="module-action">Ver módulo →</a>
                </article>
                <article class="module-card">
                    <div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/network.svg') }}" alt="" class="size-6"></span><span class="status-pill">Próximamente</span></div>
                    <div><h3>Redes</h3><p>Módulo pendiente</p></div>
                    <span class="module-disabled">No habilitado</span>
                </article>
            </div>
        </section>

        <section class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores generales">
            <article class="metric-card"><p>Dependencias registradas</p><strong class="text-brand">{{ number_format($metricas['dependencias']) }}</strong></article>
            <article class="metric-card"><p>Áreas registradas</p><strong>{{ number_format($metricas['areas']) }}</strong></article>
            <article class="metric-card"><p>Registros administrados</p><strong class="text-gold">{{ number_format($metricas['registros']) }}</strong></article>
            <article class="metric-card"><p>Alertas activas</p><strong class="text-red-500">{{ number_format($metricas['alertas']) }}</strong></article>
        </section>

        <section class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_420px]">
            <article class="panel">
                <div class="flex items-center justify-between gap-3"><h2>Actividad reciente</h2><a href="{{ route('patrimonio.movimientos.index') }}" class="text-xs font-semibold text-brand hover:underline">Ver movimientos</a></div>
                <div class="mt-4 space-y-3">
                    @forelse ($actividad as $item)
                        <a href="{{ $item['url'] }}" class="activity-item block transition hover:border-brand/30 hover:bg-surface-alt">
                            <div class="flex items-center justify-between gap-3"><span class="activity-tag activity-tag-{{ $item['tono'] }}">{{ $item['modulo'] }}</span><span class="shrink-0 text-[10px] text-muted">{{ $item['fecha']->diffForHumans() }}</span></div>
                            <p>{{ $item['descripcion'] }}</p>
                            @if ($item['detalle'])<span class="mt-1 block text-[10px] font-semibold text-muted">{{ $item['detalle'] }}</span>@endif
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-line p-8 text-center"><p class="text-sm font-semibold text-ink">Aún no hay actividad registrada</p><p class="mt-1 text-xs text-muted">Los movimientos y actualizaciones aparecerán aquí.</p></div>
                    @endforelse
                </div>
            </article>

            <aside class="panel">
                <h2>Requiere atención</h2>
                <div class="mt-4 space-y-2.5">
                    <a href="{{ route('patrimonio.bienes') }}" class="alert-card alert-danger"><img src="{{ asset('assets/icons/shield-alert.svg') }}" alt="" class="size-5"><p>Bienes sin resguardo vigente</p><b>{{ number_format($alertas['bienes_sin_resguardo']) }}</b></a>
                    <a href="{{ route('licencia.index', ['estado' => 'por_vencer']) }}" class="alert-card alert-warning"><img src="{{ asset('assets/icons/shield-alert-warning.svg') }}" alt="" class="size-5"><p>Licencias que vencen en 30 días</p><b>{{ number_format($alertas['licencias_por_vencer']) }}</b></a>
                    <a href="{{ route('patrimonio.importaciones.index') }}" class="alert-card alert-warning"><img src="{{ asset('assets/icons/shield-alert-warning.svg') }}" alt="" class="size-5"><p>Importaciones con inconsistencias</p><b>{{ number_format($alertas['importaciones_con_errores']) }}</b></a>
                </div>
                <div class="mt-5 border-t border-line pt-4"><p class="text-xs text-muted">Responsables activos</p><p class="mt-1 text-xl font-bold text-ink">{{ number_format($conteos['responsables']) }}</p><a href="{{ route('patrimonio.responsables') }}" class="mt-2 inline-block text-xs font-semibold text-brand hover:underline">Administrar responsables</a></div>
            </aside>
        </section>
    </div>
@endsection
