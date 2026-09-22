@extends('layouts.app')
@section('title', 'SICAM | Importaciones')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
    <header><h1 class="text-2xl font-bold">Importaciones</h1><p class="mt-1 text-sm text-muted">Carga bienes desde Excel o CSV con revisión previa</p></header>
    <div class="grid gap-5 xl:grid-cols-2">
        <section class="form-card" data-tour="Selecciona la categoría y descarga su plantilla. Incluye los campos generales y las características vigentes de esa categoría." data-tour-title="1. Prepara tu archivo"><h2>1. Descargar plantilla</h2>
            <form method="GET" action="{{ route('patrimonio.importaciones.plantilla') }}" data-download-form class="mt-4 space-y-4">
                <label class="block"><span class="form-label">Categoría *</span><select class="form-control" name="categoria_id" required><option value="">Selecciona una categoría</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach</select></label>
                <label class="block"><span class="form-label">Formato</span><select name="formato" class="form-control"><option value="xlsx">Excel (.xlsx)</option><option value="csv">CSV UTF-8</option></select></label><button class="action-button">Descargar plantilla</button>
            </form>
            <p class="mt-4 text-xs text-muted">Máximo 500 bienes y 5 MB. Usa una sola hoja, fechas AAAA-MM-DD y valores sin fórmulas. No cambies los encabezados. Responsabilidad y estado vacíos equivalen a sin asignar y disponible.</p>
        </section>
        @can('importaciones.crear')<section class="form-card" data-tour="Sube el archivo de la misma categoría. Primero se revisarán los datos; todavía no se crearán bienes." data-tour-title="2. Validar archivo"><h2>2. Cargar y validar</h2>
            <form method="POST" action="{{ route('patrimonio.importaciones.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">@csrf
                <label class="block"><span class="form-label">Nombre de la importación *</span><input class="form-control" name="nombre" value="{{ old('nombre') }}" required maxlength="180"></label>
                <label class="block"><span class="form-label">Categoría *</span><select class="form-control" name="categoria_id" required><option value="">Selecciona una categoría</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected(old('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>@endforeach</select></label>
                <label class="block"><span class="form-label">Archivo *</span><input class="block w-full text-sm" name="archivo" type="file" accept=".xlsx,.csv" required></label><button class="action-button action-button-primary">Validar archivo</button>
            </form>
        </section>@endcan
    </div>
    <section class="form-card"><h2>Historial de importaciones</h2><div class="mt-4 overflow-x-auto"><table class="data-table w-full min-w-[850px]"><thead><tr><th>Archivo</th><th>Categoría</th><th>Filas</th><th>Registrados</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>@forelse($importaciones as $i)<tr><td>{{ $i->nombre }}</td><td>{{ $i->categoria?->nombre }}</td><td>{{ $i->total }}</td><td>{{ $i->registrados }}</td><td>{{ str_replace('_', ' ', $i->estado) }}</td><td>{{ $i->created_at->format('d/m/Y H:i') }}</td><td><div class="flex gap-3"><a data-crud class="text-brand" href="{{ route('patrimonio.importaciones.show', $i) }}" title="Editar importación">@include('patrimonio.includes.action-icon', ['icon' => 'edit', 'label' => 'Editar importación'])</a>@can('importaciones.eliminar')<form method="POST" action="{{ route('patrimonio.importaciones.destroy', $i) }}" data-confirm="Se archivará la importación. Los bienes que ya fueron registrados se conservarán.">@csrf @method('DELETE')<button class="text-red-700" title="Eliminar importación">@include('patrimonio.includes.action-icon', ['icon' => 'delete', 'label' => 'Eliminar importación'])</button></form>@endcan</div></td></tr>@empty<tr><td colspan="7">Aún no hay importaciones.</td></tr>@endforelse</tbody></table></div>{{ $importaciones->links() }}</section>
</div>
@endsection
