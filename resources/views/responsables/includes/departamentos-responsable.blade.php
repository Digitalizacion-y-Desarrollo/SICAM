@php($dependenciasAccesos = collect($departamentos)->whereNull('parent_id'))
<div data-departamentos-form class="contents">
    <label class="block"><span class="form-label">Dependencia *</span>
        <select name="dependencia_id_accesos" class="form-control @error('dependencia_id_accesos') border-red-500 @enderror" required data-dependencia-accesos>
            <option value="">Selecciona una dependencia</option>
            @foreach ($dependenciasAccesos as $dependencia)
                <option value="{{ $dependencia['id'] }}" @selected((string) $dependenciaValue === (string) $dependencia['id'])>{{ $dependencia['nombre'] }}</option>
            @endforeach
        </select>
    </label>
    <label class="block"><span class="form-label">Área{{ ($areaRequired ?? false) ? ' *' : '' }}</span>
        <select name="area_id_accesos" class="form-control @error('area_id_accesos') border-red-500 @enderror" @required($areaRequired ?? false) data-area-accesos>
            <option value="">Selecciona un área</option>
        </select>
        <span class="mt-1 block text-xs text-muted" data-areas-accesos-ayuda>Selecciona una dependencia para consultar sus áreas.</span>
    </label>
    <script type="application/json" data-departamentos-accesos>@json($departamentos)</script>
</div>
