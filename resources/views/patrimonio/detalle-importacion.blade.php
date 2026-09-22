@extends('layouts.app')
@section('title', 'SICAM | Importación')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
    <header><h1 class="text-2xl font-bold">{{ $importacion->nombre }}</h1><p class="mt-1 text-sm text-muted">{{ $importacion->archivo_original }} · {{ $importacion->categoria?->nombre }} · {{ str_replace('_', ' ', $importacion->estado) }}</p></header>
    @can('importaciones.editar')<form method="POST" action="{{ route('patrimonio.importaciones.update', $importacion) }}" class="form-card flex flex-wrap items-end gap-3">@csrf @method('PUT')<label class="min-w-0 flex-1"><span class="form-label">Nombre de la importación</span><input class="form-control" name="nombre" value="{{ old('nombre', $importacion->nombre) }}" required maxlength="180"></label><button class="action-button">Guardar nombre</button></form>@endcan
    <section class="form-card" data-tour="Revisa los errores y los nombres de los bienes. Confirma únicamente cuando la vista previa sea correcta." data-tour-title="3. Confirmar el registro">
        <div class="flex flex-wrap justify-between gap-4"><h2>Vista previa · {{ $importacion->total }} bienes</h2>
            @if($importacion->estado === 'PREVIA')@can('importaciones.editar')<form method="POST" action="{{ route('patrimonio.importaciones.confirmar', $importacion) }}" data-confirm="Se registrarán {{ $importacion->total }} bienes con sus folios y QR. ¿Deseas continuar?">@csrf<button class="action-button action-button-primary">Confirmar importación</button></form>@endcan
@endif
        </div>
        @if($importacion->errores)<p class="mt-3 text-sm text-red-700">Corrige las filas indicadas y carga nuevamente el archivo desde Importaciones.</p>@endif
        <div class="mt-4 max-h-[600px] overflow-auto"><table class="data-table w-full min-w-[650px]"><thead><tr><th>Fila</th><th>Nombre</th><th>Ubicación</th><th>Revisión</th></tr></thead><tbody>@foreach($importacion->filas as $fila)<tr><td>{{ $fila['numero'] }}</td><td>{{ $fila['datos']['nombre'] ?? 'Sin nombre' }}</td><td>{{ $fila['datos']['dependencia_id_accesos'] ?? '—' }} / {{ $fila['datos']['area_id_accesos'] ?? '—' }}</td><td>@forelse($importacion->errores[$fila['numero']] ?? [] as $error)<p class="text-red-700">{{ $error }}</p>@empty<span class="text-emerald-700">Válida</span>@endforelse</td></tr>@endforeach</tbody></table></div>
    </section>
    @if($importacion->estado === 'COMPLETADA')<section class="form-card"><h2>Bienes registrados</h2><div class="mt-3 space-y-2">@foreach($bienes as $bien)<a data-crud class="block text-sm text-brand" href="{{ route('patrimonio.bienes.show', $bien) }}">{{ $bien->folio_sicam }} · {{ $bien->nombre }}</a>@endforeach</div><div class="mt-4">{{ $bienes->links() }}</div></section>@endif
    <a class="action-button" href="{{ route('patrimonio.importaciones.index') }}">Volver a importaciones</a>
</div>
@endsection
