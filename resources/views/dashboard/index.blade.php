@extends('layouts.app')

@section('title', 'SICAM | Inicio')

@section('content')
    <div class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-8">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h1 class="text-2xl font-bold tracking-tight sm:text-[26px]">Inicio</h1><p class="mt-1 text-xs text-muted sm:text-sm">Visión general del control de activos municipales</p></div>
            <p class="flex items-center gap-2 text-[11px] text-muted"><span class="size-1.5 rounded-full bg-emerald-500"></span>Última actualización: Hoy, 10:25</p>
        </div>

        <section><h2 class="section-heading">Módulos del sistema</h2><div class="mt-2.5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="module-card module-card-active"><div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/building.svg') }}" alt="" class="size-6"></span></div><div><h3>Patrimonio</h3><p>1,248 bienes <span>•</span> 18 dependencias</p></div><a href="#" class="module-action">Ver módulo →</a></article>
            <article class="module-card"><div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/terminal.svg') }}" alt="" class="size-6"></span><span class="status-pill">Próximamente</span></div><div><h3>Software</h3><p>48 sistemas</p></div><span class="module-disabled">No habilitado</span></article>
            <article class="module-card"><div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/file-check.svg') }}" alt="" class="size-6"></span><span class="status-pill">Próximamente</span></div><div><h3>Licencias</h3><p>312 licencias</p></div><span class="module-disabled">No habilitado</span></article>
            <article class="module-card"><div class="flex items-start justify-between"><span class="icon-tile"><img src="{{ asset('assets/icons/network.svg') }}" alt="" class="size-6"></span><span class="status-pill">Próximamente</span></div><div><h3>Redes</h3><p>428 elementos</p></div><span class="module-disabled">No habilitado</span></article>
        </div></section>

        <section class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card"><p>Dependencias</p><strong class="text-brand">18</strong></article><article class="metric-card"><p>Áreas municipales</p><strong>73</strong></article><article class="metric-card"><p>Registros administrados</p><strong class="text-gold">1,248</strong></article><article class="metric-card"><p>Alertas activas</p><strong class="text-red-500">3</strong></article>
        </section>

        <section class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_420px]">
            <article class="panel"><h2>Actividad reciente</h2><div class="mt-4 space-y-3">
                @foreach ([['PATRIMONIO','Hace 10 min - Folio SICAM-2024-0082','Alta de bien: 24 Computadoras Dell Latitude para Tesorería','brand'],['IMPORTACIÓN','Hace 45 min - Por: Juan Pérez','Carga masiva de inventario: Parque vehicular de Servicios Públicos (120 vehículos)','gold'],['PATRIMONIO','Hace 2 horas - Folio RES-2024-1102','Reasignación de resguardo: Impresora Multifuncional HP de Oficina de Presidencia a Catastro','brand'],['SISTEMAS','Hace 4 horas - Automático','Depuración programada: 12 licencias caducas dadas de baja del inventario activo','gold']] as [$module, $time, $description, $tone])
                    <div class="activity-item"><div class="flex items-center justify-between gap-3"><span class="activity-tag activity-tag-{{ $tone }}">{{ $module }}</span><span class="shrink-0 text-[10px] text-muted">{{ $time }}</span></div><p>{{ $description }}</p></div>
                @endforeach
            </div></article>
            <aside class="panel"><h2>Requiere atención</h2><div class="mt-4 space-y-2.5"><div class="alert-card alert-danger"><img src="{{ asset('assets/icons/shield-alert.svg') }}" alt="" class="size-5"><p>Bienes sin responsable asignado</p><b>12</b></div><div class="alert-card alert-danger"><img src="{{ asset('assets/icons/shield-alert.svg') }}" alt="" class="size-5"><p>Importaciones con inconsistencias/errores</p><b>2</b></div><div class="alert-card alert-warning"><img src="{{ asset('assets/icons/shield-alert-warning.svg') }}" alt="" class="size-5"><p>Registros incompletos en Catastro</p><b>8</b></div></div></aside>
        </section>
    </div>
@endsection
