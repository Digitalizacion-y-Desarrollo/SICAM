@extends('layouts.app')
@section('title', 'SICAM | Resguardo')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<h1 class="text-2xl font-bold">{{ $asignacion ? 'Reasignar bien' : 'Nueva asignación' }}</h1><form method="POST" action="{{ $asignacion ? route('patrimonio.asignaciones.update', $asignacion) : route('patrimonio.asignaciones.store') }}" class="form-card space-y-4" data-asignacion-form>@csrf @if($asignacion) @method('PUT') @endif
<label class="block"><span class="form-label">Bien *</span><select name="bien_id" class="form-control" required><option value="">Selecciona un bien</option>@foreach($bienes as $bien)<option value="{{ $bien->id }}" @selected(old('bien_id', $asignacion?->bien_id ?? request('bien')) == $bien->id)>{{ $bien->folio_sicam }} · {{ $bien->nombre }}</option>@endforeach</select></label>
<label class="block"><span class="form-label">Tipo de responsabilidad *</span><select class="form-control" name="responsabilidad" data-asignacion-tipo><option value="persona" @selected(old('responsabilidad', strtolower($asignacion?->tipo_responsabilidad ?? 'persona')) === 'persona')>Persona</option><option value="area" @selected(old('responsabilidad', strtolower($asignacion?->tipo_responsabilidad ?? 'persona')) === 'area')>Área</option></select></label>
<label class="block"><span class="form-label">Responsable (para asignación a persona)</span><select class="form-control" name="responsable_id" data-asignacion-responsable><option value="">Selecciona un responsable</option>@foreach($responsables as $r)<option value="{{ $r->id }}" data-dependencia="{{ $r->dependencia_id_accesos }}" data-area="{{ $r->area_id_accesos }}" @selected(old('responsable_id', $asignacion?->responsable_id) == $r->id)>{{ $r->nombre_completo }} · {{ $r->dependencia_id_accesos }}{{ $r->area_id_accesos ? ' / '.$r->area_id_accesos : '' }}</option>@endforeach</select><span class="mt-1 block text-xs text-muted">La dependencia y el área se tomarán de los datos del responsable.</span></label>
<div class="grid gap-4 sm:grid-cols-2">
@include('responsables.includes.departamentos-responsable', [
    'prefix' => 'asignacion',
    'dependenciaValue' => old('dependencia_id_accesos', $asignacion?->dependencia_id_accesos),
    'areaValue' => old('area_id_accesos', $asignacion?->area_id_accesos),
    'areaRequired' => false,
])
</div>
<label class="block"><span class="form-label">Estado</span><select class="form-control" name="estado"><option value="ASIGNADO">Asignado</option><option value="EN_RESGUARDO" @selected(old('estado') === 'EN_RESGUARDO')>En resguardo</option></select></label>
<label class="block"><span class="form-label">Observaciones</span><textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea></label><div class="flex gap-3"><button class="action-button action-button-primary">Guardar resguardo</button><a href="{{ route('patrimonio.asignaciones.index') }}" class="action-button">Cancelar</a></div></form>
</div>
@endsection
