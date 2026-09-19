@extends('layouts.app')
@section('title', 'SICAM | Nota de movimiento')
@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">
<h1 class="text-2xl font-bold">{{ isset($movimiento) ? 'Editar observaciones' : 'Registrar nota de seguimiento' }}</h1><form method="POST" action="{{ isset($movimiento) ? route('patrimonio.movimientos.update', $movimiento) : route('patrimonio.movimientos.store') }}" class="form-card space-y-4">@csrf @isset($movimiento) @method('PUT') @endisset
@isset($bienes)<label class="block"><span class="form-label">Bien *</span><select name="bien_id" class="form-control" required><option value="">Selecciona un bien</option>@foreach($bienes as $bien)<option value="{{ $bien->id }}" @selected(old('bien_id') == $bien->id)>{{ $bien->folio_sicam }} · {{ $bien->nombre }}</option>@endforeach</select></label>@endisset
<label class="block"><span class="form-label">Observaciones *</span><textarea name="observaciones" class="form-control" rows="5" required maxlength="5000">{{ old('observaciones', $movimiento->observaciones ?? '') }}</textarea></label><div class="flex gap-3"><button class="action-button action-button-primary">Guardar cambios</button><a class="action-button" href="{{ route('patrimonio.movimientos.index') }}">Cancelar</a></div></form>
</div>
@endsection
