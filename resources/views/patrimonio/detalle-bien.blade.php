@extends('layouts.app')
@section('title', 'SICAM | '.$bien->nombre)
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
    <a href="{{ route('patrimonio.bienes') }}" class="text-sm text-brand">← Bienes patrimoniales</a>
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div><p class="text-sm font-semibold text-brand">{{ $bien->folio_sicam }}</p><h1 class="mt-2 text-2xl font-bold">{{ $bien->nombre }}</h1><p class="mt-1 text-sm text-muted">{{ $bien->categoria?->nombre }} · {{ str_replace('_', ' ', $bien->estado) }}</p></div>
        <div class="flex flex-wrap gap-2" data-tour="Puedes editar el bien o descargar su QR para imprimir una etiqueta." data-tour-title="Acciones del bien">
            @can('bienes.editar')
                <a data-crud href="{{ route('patrimonio.bienes.edit', $bien) }}" class="action-button">Editar</a>
            @endcan
            <a data-download data-filename="{{ $bien->folio_sicam }}.png" href="{{ route('patrimonio.bienes.qr', ['bien' => $bien, 'formato' => 'png']) }}" class="action-button action-button-primary">Descargar QR</a>
            @can('bienes.eliminar')
                <form method="POST" action="{{ route('patrimonio.bienes.destroy', $bien) }}">@csrf @method('DELETE')<button class="action-button text-red-700">Eliminar</button></form>
            @endcan
        </div>
    </header>
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="space-y-5">
            <section class="form-card" data-tour="Esta ficha reúne los datos generales, la ubicación y las características capturadas." data-tour-title="Información del activo"><h2>Información del bien</h2>
                <dl class="mt-4 grid gap-5 sm:grid-cols-2">
                    @foreach(['numero_patrimonial' => 'No. patrimonial', 'numero_serie' => 'No. de serie', 'marca' => 'Marca', 'modelo' => 'Modelo', 'dependencia_id_accesos' => 'Dependencia', 'area_id_accesos' => 'Área', 'ubicacion_fisica' => 'Ubicación física', 'fecha_adquisicion' => 'Fecha de adquisición', 'costo' => 'Costo de adquisición', 'proveedor' => 'Proveedor', 'numero_factura' => 'No. de factura', 'observaciones' => 'Observaciones'] as $key => $label)
                        <div><dt class="text-xs text-muted">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-semibold">{{ $bien->$key ?? 'No registrado' }}</dd></div>
                    @endforeach
                </dl>
            </section>
            <section class="form-card"><h2>Características</h2><dl class="mt-4 grid gap-4 sm:grid-cols-2">@forelse($bien->valores as $valor)<div><dt class="text-xs text-muted">{{ $valor->campo?->nombre ?? 'Campo retirado' }}</dt><dd class="mt-1 text-sm">{{ $valor->valor ?? 'No registrado' }}</dd></div>@empty<p class="text-sm text-muted">Sin características adicionales.</p>@endforelse</dl></section>
            <section class="form-card" data-tour="Aquí puedes consultar las altas, modificaciones y cambios de resguardo del bien." data-tour-title="Historial"><h2>Movimientos</h2><div class="mt-4 space-y-3">@forelse($movimientos as $movimiento)<div class="rounded-lg border border-line p-3 text-sm"><p class="font-semibold">{{ str_replace('_', ' ', $movimiento->tipo) }}</p><p class="mt-1 text-xs text-muted">{{ $movimiento->created_at->format('d/m/Y H:i') }}</p><p class="mt-1">{{ $movimiento->observaciones }}</p></div>@empty<p class="text-sm text-muted">Sin movimientos.</p>@endforelse</div><div class="mt-4">{{ $movimientos->links() }}</div></section>
        </div>
        <aside class="space-y-5">
            @if($bien->fotografia_path)<section class="form-card"><img src="{{ route('bienes.publico.fotografia', $bien->public_id) }}" alt="Fotografía del bien" class="max-h-64 w-full object-contain"></section>@endif
            <section class="form-card text-center"><h2>Etiqueta QR</h2><img src="data:image/svg+xml;base64,{{ base64_encode(app(\App\Services\QrBien::class)->imagen($bien)) }}" alt="QR de la ficha pública" class="mx-auto mt-3 size-52 max-w-full"><p class="text-xs font-semibold">{{ $bien->numero_patrimonial }}</p><a href="{{ route('bienes.publico', $bien->public_id) }}" target="_blank" rel="noopener" class="mt-3 inline-block text-sm text-brand">Abrir ficha pública</a></section>
            <section class="form-card"><h2>Resguardo actual</h2><p class="mt-3 text-sm">{{ $bien->asignacionActual?->responsable?->nombre_completo ?? ($bien->asignacionActual ? 'Área: '.$bien->area_id_accesos : 'Sin asignar') }}</p></section>
        </aside>
    </div>
</div>
@endsection
