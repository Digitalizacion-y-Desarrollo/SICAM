@extends('layouts.app')
@section('title', 'SICAM | Detalle de movimiento')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<header class="flex flex-wrap justify-between gap-4"><div><h1 class="text-2xl font-bold">{{ $movimiento->tipo }} #{{ $movimiento->id }}</h1><p class="mt-1 text-sm text-muted">{{ $movimiento->bien?->folio_sicam }} · {{ $movimiento->created_at->format('d/m/Y H:i') }}</p></div><a data-crud class="action-button" href="{{ route('patrimonio.movimientos.edit', $movimiento) }}">Editar observaciones</a></header><section class="form-card"><h2>Observaciones</h2><p class="mt-3 whitespace-pre-wrap text-sm">{{ $movimiento->observaciones ?: 'Sin observaciones.' }}</p></section><div class="grid gap-5 xl:grid-cols-2">@foreach(['valor_anterior' => 'Antes', 'valor_nuevo' => 'Después'] as $key => $label)<section class="form-card"><h2>{{ $label }}</h2><dl class="mt-4 space-y-3">@forelse($movimiento->$key ?? [] as $field => $value)<div><dt class="text-xs text-muted">{{ str_replace('_', ' ', $field) }}</dt><dd class="break-all text-sm">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? '—') }}</dd></div>@empty<p class="text-sm text-muted">Sin datos.</p>@endforelse</dl></section>@endforeach</div>
</div>
@endsection
