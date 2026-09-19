@extends('layouts.app')
@section('title', 'SICAM | Editar categoría')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<h1 class="text-2xl font-bold">Editar categoría</h1>
<form method="POST" action="{{ route('patrimonio.categorias.update', $categoria) }}" class="form-card space-y-4">@csrf @method('PUT')
<label class="block"><span class="form-label">Nombre *</span><input class="form-control" name="nombre" type="text" value="{{ old('nombre', $categoria->nombre) }}" required></label>
<label class="block"><span class="form-label">Categoría padre</span><select class="form-control" name="categoria_padre_id"><option value="">Categoría principal</option>@foreach($categorias as $opcion)<option value="{{ $opcion->id }}" @selected(old('categoria_padre_id', $categoria->categoria_padre_id) == $opcion->id)>{{ $opcion->nombre }}</option>@endforeach</select></label>
<label class="block"><span class="form-label">Descripción</span><textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $categoria->descripcion) }}</textarea></label>
<input type="hidden" name="activo" value="0"><label class="block text-sm"><input type="checkbox" name="activo" value="1" @checked(old('activo', $categoria->activo))> Categoría activa</label>
<div class="flex gap-3"><button class="action-button action-button-primary">Guardar cambios</button><a class="action-button" href="{{ route('patrimonio.categorias', ['categoria' => $categoria]) }}">Cancelar</a></div></form>
</div>
@endsection
