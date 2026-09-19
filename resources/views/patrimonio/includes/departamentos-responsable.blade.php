@php($dependenciasAccesos = collect($departamentos)->whereNull('parent_id'))
<div data-departamentos-form class="contents">
    <label class="block"><span class="form-label">Dependencia *</span>
        <input name="dependencia_id_accesos" list="{{ $prefix }}-dependencias" value="{{ $dependenciaValue }}" class="form-control" maxlength="100" autocomplete="off" placeholder="Selecciona una dependencia" required data-dependencia-accesos>
        <datalist id="{{ $prefix }}-dependencias">
            @foreach ($dependenciasAccesos as $dependencia)
                <option value="{{ $dependencia['nombre'] }}"></option>
            @endforeach
        </datalist>
    </label>
    <label class="block"><span class="form-label">Área{{ ($areaRequired ?? false) ? ' *' : '' }}</span>
        <input name="area_id_accesos" list="{{ $prefix }}-areas" value="{{ $areaValue }}" class="form-control" maxlength="100" autocomplete="off" placeholder="Selecciona un área" @required($areaRequired ?? false) data-area-accesos>
        <datalist id="{{ $prefix }}-areas" data-areas-accesos></datalist>
        <span class="mt-1 block text-xs text-muted" data-areas-accesos-ayuda>Selecciona una dependencia para consultar sus áreas.</span>
    </label>
    <script type="application/json" data-departamentos-accesos>@json($departamentos)</script>
</div>
