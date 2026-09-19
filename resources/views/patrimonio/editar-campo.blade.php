@extends('layouts.app')
@section('title', 'SICAM | Editar campo')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<h1 class="text-2xl font-bold">Editar campo</h1><form method="POST" action="{{ route('patrimonio.campos.update', $campo) }}" class="form-card space-y-4">@csrf @method('PUT')
<label class="block"><span class="form-label">Nombre *</span><input class="form-control" name="nombre" type="text" value="{{ old('nombre', $campo->nombre) }}" required></label>
<label class="block"><span class="form-label">Tipo</span><select name="tipo" class="form-control">@foreach(['TEXTO' => 'Texto', 'NUMERO' => 'Número', 'FECHA' => 'Fecha', 'SELECCION' => 'Selección'] as $key => $label)<option value="{{ $key }}" @selected(old('tipo', $campo->tipo) === $key)>{{ $label }}</option>@endforeach</select></label>
<label class="block"><span class="form-label">Opciones de selección (una por línea)</span><textarea name="opciones" class="form-control" rows="5">{{ implode("\n", (array) old('opciones', $campo->opciones ?? [])) }}</textarea></label>
<label class="block"><span class="form-label">Orden *</span><input class="form-control" name="orden" type="number" value="{{ old('orden', $campo->orden) }}" required></label>
<input type="hidden" name="requerido" value="0"><label class="block text-sm"><input type="checkbox" name="requerido" value="1" @checked(old('requerido', $campo->requerido))> Obligatorio</label>
<input type="hidden" name="activo" value="0"><label class="block text-sm"><input type="checkbox" name="activo" value="1" @checked(old('activo', $campo->activo))> Activo</label>
<div class="flex gap-3"><button class="action-button action-button-primary">Guardar cambios</button><a class="action-button" href="{{ route('patrimonio.categorias', ['categoria' => $campo->categoria_id]) }}">Cancelar</a></div></form>
</div>
@endsection
