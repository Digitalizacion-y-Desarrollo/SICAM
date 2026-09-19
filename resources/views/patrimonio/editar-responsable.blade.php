@extends('layouts.app')
@section('title', 'SICAM | Editar responsable')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<h1 class="text-2xl font-bold">Editar responsable</h1><form method="POST" action="{{ route('patrimonio.responsables.update', $responsable) }}" class="form-card">@csrf @method('PUT')
<div class="grid gap-4 sm:grid-cols-2">
<label class="block"><span class="form-label">Número de empleado</span><input class="form-control" name="numero_empleado" type="text" value="{{ old('numero_empleado', $responsable->numero_empleado) }}" ></label>
<label class="block"><span class="form-label">Nombre *</span><input class="form-control" name="nombre" type="text" value="{{ old('nombre', $responsable->nombre) }}" required></label>
<label class="block"><span class="form-label">Apellido paterno</span><input class="form-control" name="apellido_paterno" type="text" value="{{ old('apellido_paterno', $responsable->apellido_paterno) }}" ></label>
<label class="block"><span class="form-label">Apellido materno</span><input class="form-control" name="apellido_materno" type="text" value="{{ old('apellido_materno', $responsable->apellido_materno) }}" ></label>
<label class="block"><span class="form-label">Cargo</span><input class="form-control" name="cargo" type="text" value="{{ old('cargo', $responsable->cargo) }}" ></label>
@include('patrimonio.includes.departamentos-responsable', [
    'prefix' => 'editar-responsable',
    'dependenciaValue' => old('dependencia_id_accesos', $responsable->dependencia_id_accesos),
    'areaValue' => old('area_id_accesos', $responsable->area_id_accesos),
])
<label class="block"><span class="form-label">Correo</span><input class="form-control" name="correo" type="email" value="{{ old('correo', $responsable->correo) }}" ></label>
<label class="block"><span class="form-label">Teléfono</span><input class="form-control" name="telefono" type="text" value="{{ old('telefono', $responsable->telefono) }}" ></label>
</div><input type="hidden" name="activo" value="0"><label class="my-4 block text-sm"><input type="checkbox" name="activo" value="1" @checked(old('activo', $responsable->activo))> Responsable activo</label><div class="flex gap-3"><button class="action-button action-button-primary">Guardar cambios</button><a class="action-button" href="{{ route('patrimonio.responsables.show', $responsable) }}">Cancelar</a></div></form>
</div>
@endsection
