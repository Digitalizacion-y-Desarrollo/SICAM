@extends('layouts.app')
@section('title', 'SICAM | Detalle de asignación')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<header class="flex flex-wrap justify-between gap-4"><h1 class="text-2xl font-bold">Asignación #{{ $asignacion->id }}</h1>@unless($asignacion->fecha_fin)<a data-crud class="action-button" href="{{ route('patrimonio.asignaciones.edit', $asignacion) }}">Reasignar</a>@endunless</header><section class="form-card"><h2>{{ $asignacion->bien?->nombre }}</h2><p class="mt-2 text-brand">{{ $asignacion->bien?->folio_sicam }}</p><dl class="mt-5 grid gap-5 sm:grid-cols-2">
@foreach(['Responsable' => $asignacion->responsable?->nombre_completo ?? 'Área', 'Dependencia' => $asignacion->dependencia_id_accesos, 'Área' => $asignacion->area_id_accesos, 'Inicio' => $asignacion->fecha_inicio->format('d/m/Y H:i'), 'Fin' => $asignacion->fecha_fin?->format('d/m/Y H:i') ?? 'Vigente', 'Observaciones' => $asignacion->observaciones] as $label => $value)<div><dt class="text-xs text-muted">{{ $label }}</dt><dd class="mt-1 text-sm">{{ $value ?: 'No registrado' }}</dd></div>@endforeach</dl></section><a href="{{ route('patrimonio.asignaciones.index') }}" class="action-button">Volver a asignaciones</a>
</div>
@endsection
