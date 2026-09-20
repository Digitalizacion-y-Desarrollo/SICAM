@extends('layouts.app')

@section('title', 'SICAM | Editar software')

@section('content')

    @php
        $fieldValue = fn($key, $default = null) => old($key, data_get($sistema, $key, $default));

        $dependenciasAccesos = collect($departamentos ?? [])->whereNull('parent_id');

        $responsableFuncional = collect($responsables ?? [])->firstWhere('id', $fieldValue('responsable_funcional_id'));

        $responsableTecnico = collect($responsables ?? [])->firstWhere('id', $fieldValue('responsable_tecnico_id'));
    @endphp


    <form id="editar-software" class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-7"
        action="{{ route('software.sistemas.update', $sistema) }}" method="POST" novalidate>

        @csrf
        @method('PUT')


        {{-- ENCABEZADO --}}
        <div class="mb-4">

            <h1 class="text-2xl font-bold text-ink">
                Editar software
            </h1>

            <p class="mt-1 text-sm text-muted">
                Actualiza la información del sistema o aplicación.
            </p>

        </div>


        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">


            {{-- ================================================= --}}
            {{-- COLUMNA PRINCIPAL --}}
            {{-- ================================================= --}}

            <div class="space-y-4">


                {{-- ================================================= --}}
                {{-- INFORMACIÓN GENERAL --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Información general
                    </h2>

                    <p class="mt-1 text-sm text-muted">
                        Datos principales para identificar el sistema o aplicación.
                    </p>


                    <div class="mt-4 grid gap-4 sm:grid-cols-2">


                        {{-- CLAVE --}}
                        <label class="block">

                            <span class="form-label">
                                Clave *
                            </span>

                            <input type="text" name="clave" value="{{ $fieldValue('clave') }}" class="form-control"
                                maxlength="50" placeholder="Ej. SICASI" required>

                            @error('clave')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>


                        {{-- NOMBRE --}}
                        <label class="block">

                            <span class="form-label">
                                Nombre del aplicativo *
                            </span>

                            <input type="text" name="nombre" value="{{ $fieldValue('nombre') }}" class="form-control"
                                maxlength="180" placeholder="Nombre del sistema" required>

                            @error('nombre')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>


                        {{-- TIPO --}}
                        <label class="block">

                            <span class="form-label">
                                Tipo *
                            </span>

                            <select name="tipo" class="form-control" required>
                                <option value="">
                                    Selecciona un tipo
                                </option>

                                @foreach (\App\Models\Sistema::TIPOS as $value => $label)
                                    <option value="{{ $value }}" @selected($fieldValue('tipo') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>

                            @error('tipo')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>


                        {{-- ORIGEN --}}
                        <label class="block">

                            <span class="form-label">
                                Origen *
                            </span>

                            <select name="origen" class="form-control" required>
                                <option value="">
                                    Selecciona el origen
                                </option>

                                @foreach (\App\Models\Sistema::ORIGENES as $value => $label)
                                    <option value="{{ $value }}" @selected($fieldValue('origen') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>

                            @error('origen')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>

                    </div>


                    {{-- DESCRIPCIÓN --}}
                    <label class="mt-4 block">

                        <span class="form-label">
                            Descripción *
                        </span>

                        <textarea name="descripcion" class="form-control h-auto min-h-28 p-3" rows="4" maxlength="5000"
                            placeholder="Describe brevemente qué es y qué hace el sistema." required>{{ $fieldValue('descripcion') }}</textarea>


                        @error('descripcion')
                            <span class="mt-1 block text-xs text-red-700">
                                {{ $message }}
                            </span>
                        @enderror

                    </label>


                    {{-- OBJETIVO --}}
                    <label class="mt-4 block">

                        <span class="form-label">
                            Objetivo
                        </span>

                        <textarea name="objetivo" class="form-control h-auto min-h-28 p-3" rows="4" maxlength="5000"
                            placeholder="Indica el objetivo principal del sistema.">{{ $fieldValue('objetivo') }}</textarea>


                        @error('objetivo')
                            <span class="mt-1 block text-xs text-red-700">
                                {{ $message }}
                            </span>
                        @enderror

                    </label>

                </section>


                {{-- ================================================= --}}
                {{-- RESPONSABLES --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Responsables
                    </h2>

                    <p class="mt-1 text-sm text-muted">
                        Personal responsable de la operación funcional y técnica del sistema.
                    </p>


                    <div class="mt-4 grid gap-4 sm:grid-cols-2">


                        {{-- RESPONSABLE FUNCIONAL --}}
                        <div>

                            <label class="block">

                                <span class="form-label">
                                    Responsable funcional
                                </span>


                                <input type="text" id="responsable-funcional" list="responsables-funcionales"
                                    class="form-control" placeholder="Buscar responsable..." autocomplete="off"
                                    value="{{ $responsableFuncional?->nombre_completo }}">


                                <datalist id="responsables-funcionales">

                                    @foreach ($responsables ?? [] as $responsable)
                                        <option value="{{ $responsable->nombre_completo }}"
                                            data-id="{{ $responsable->id }}">

                                            @if ($responsable->cargo)
                                                {{ $responsable->cargo }}
                                            @endif

                                            @if ($responsable->dependencia_id_accesos)
                                                · {{ $responsable->dependencia_id_accesos }}
                                            @endif

                                        </option>
                                    @endforeach

                                </datalist>


                                <input type="hidden" name="responsable_funcional_id" id="responsable-funcional-id"
                                    value="{{ $fieldValue('responsable_funcional_id') }}">


                                @error('responsable_funcional_id')
                                    <span class="mt-1 block text-xs text-red-700">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </label>


                            <span class="mt-1 block text-xs text-muted">
                                Responsable del funcionamiento y operación administrativa.
                            </span>

                        </div>


                        {{-- RESPONSABLE TÉCNICO --}}
                        <div>

                            <label class="block">

                                <span class="form-label">
                                    Responsable técnico
                                </span>


                                <input type="text" id="responsable-tecnico" list="responsables-tecnicos"
                                    class="form-control" placeholder="Buscar responsable..." autocomplete="off"
                                    value="{{ $responsableTecnico?->nombre_completo }}">


                                <datalist id="responsables-tecnicos">

                                    @foreach ($responsables ?? [] as $responsable)
                                        <option value="{{ $responsable->nombre_completo }}"
                                            data-id="{{ $responsable->id }}">

                                            @if ($responsable->cargo)
                                                {{ $responsable->cargo }}
                                            @endif

                                            @if ($responsable->dependencia_id_accesos)
                                                · {{ $responsable->dependencia_id_accesos }}
                                            @endif

                                        </option>
                                    @endforeach

                                </datalist>


                                <input type="hidden" name="responsable_tecnico_id" id="responsable-tecnico-id"
                                    value="{{ $fieldValue('responsable_tecnico_id') }}">


                                @error('responsable_tecnico_id')
                                    <span class="mt-1 block text-xs text-red-700">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </label>


                            <span class="mt-1 block text-xs text-muted">
                                Responsable del desarrollo, soporte y mantenimiento técnico.
                            </span>

                        </div>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- ACCESO Y REPOSITORIO --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Acceso y repositorio
                    </h2>

                    <p class="mt-1 text-sm text-muted">
                        Enlaces principales relacionados con el sistema.
                    </p>


                    <div class="mt-4 space-y-4">


                        {{-- URL PRODUCCIÓN --}}
                        <label class="block">

                            <span class="form-label">
                                URL de producción
                            </span>

                            <input type="url" name="url_produccion" value="{{ $fieldValue('url_produccion') }}"
                                class="form-control" maxlength="500" placeholder="https://sistema.neza.gob.mx">


                            <span class="mt-1 block text-xs text-muted">
                                Dirección pública o interna donde se encuentra disponible el sistema.
                            </span>


                            @error('url_produccion')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>


                        {{-- REPOSITORIO --}}
                        <label class="block">

                            <span class="form-label">
                                Repositorio
                            </span>

                            <input type="url" name="repositorio_url" value="{{ $fieldValue('repositorio_url') }}"
                                class="form-control" maxlength="500" placeholder="https://github.com/...">


                            <span class="mt-1 block text-xs text-muted">
                                Repositorio donde se administra el código fuente del proyecto.
                            </span>


                            @error('repositorio_url')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>

                    </div>

                </section>

            </div>


            {{-- ================================================= --}}
            {{-- SIDEBAR --}}
            {{-- ================================================= --}}

            <aside class="space-y-4">


                {{-- ================================================= --}}
                {{-- UBICACIÓN ADMINISTRATIVA --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Ubicación administrativa
                    </h2>

                    <p class="mt-1 text-xs text-muted">
                        Dependencia y área responsables del sistema.
                    </p>


                    <div class="mt-4 space-y-4">


                        {{-- DEPENDENCIA --}}
                        <label class="block">

                            <span class="form-label">
                                Dependencia *
                            </span>

                            <input name="dependencia_id_accesos" id="software-dependencia" list="dependencias-accesos"
                                value="{{ $fieldValue('dependencia_id_accesos') }}" class="form-control" maxlength="100"
                                autocomplete="off" required>


                            <datalist id="dependencias-accesos">

                                @foreach ($dependenciasAccesos as $dependencia)
                                    <option value="{{ $dependencia['nombre'] }}"></option>
                                @endforeach

                            </datalist>


                            @error('dependencia_id_accesos')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>


                        {{-- ÁREA --}}
                        <label id="software-area-field" class="block" hidden>

                            <span class="form-label">
                                Área
                            </span>

                            <input name="area_id_accesos" id="software-area" list="areas-accesos"
                                value="{{ $fieldValue('area_id_accesos') }}" class="form-control" maxlength="100"
                                autocomplete="off">


                            <datalist id="areas-accesos"></datalist>


                            <span id="areas-accesos-ayuda" class="mt-1 block text-xs text-muted">
                                Selecciona una dependencia para consultar sus áreas.
                            </span>


                            @error('area_id_accesos')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>

                    </div>


                    <div class="mt-4 rounded-lg bg-surface-alt p-3">

                        <div class="flex gap-2">

                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">

                                <circle cx="12" cy="12" r="10" />

                                <path d="M12 16v-4" />

                                <path d="M12 8h.01" />

                            </svg>


                            <p class="text-xs leading-5 text-muted">

                                La estructura organizacional se obtiene desde

                                <strong class="font-semibold text-ink">
                                    Accesos
                                </strong>.

                            </p>

                        </div>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- ESTADO --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Estado del sistema
                    </h2>

<label class="mt-4 block">

    <span class="form-label">
        Estado *
    </span>

    <select
        name="estado"
        class="form-control"
        required
    >

        @foreach (\App\Models\Sistema::ESTADOS as $value => $label)

            <option
                value="{{ $value }}"
                @selected($fieldValue('estado', 'desarrollo') === $value)
            >
                {{ $label }}
            </option>

        @endforeach

    </select>

    @error('estado')
        <span class="mt-1 block text-xs text-red-700">
            {{ $message }}
        </span>
    @enderror

</label>


                    <p class="mt-3 text-xs leading-5 text-muted">
                        El estado representa la etapa actual del ciclo de vida del sistema.
                    </p>

                </section>


                {{-- ================================================= --}}
                {{-- CICLO DE VIDA --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Ciclo de vida
                    </h2>


                    <div class="mt-4 space-y-4">


                        {{-- FECHA INICIO --}}
                        <label class="block">

                            <span class="form-label">
                                Fecha de inicio
                            </span>

                            <input type="date" name="fecha_inicio" value="{{ $fieldValue('fecha_inicio') }}"
                                class="form-control">


                            @error('fecha_inicio')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>


                        {{-- FECHA LIBERACIÓN --}}
                        <label class="block">

                            <span class="form-label">
                                Fecha de liberación
                            </span>

                            <input type="date" name="fecha_liberacion" value="{{ $fieldValue('fecha_liberacion') }}"
                                class="form-control">


                            @error('fecha_liberacion')
                                <span class="mt-1 block text-xs text-red-700">
                                    {{ $message }}
                                </span>
                            @enderror

                        </label>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- INFORMACIÓN DEL REGISTRO --}}
                {{-- ================================================= --}}

                <section class="form-card">

                    <h2>
                        Información del registro
                    </h2>


                    <dl class="mt-4 space-y-3 text-sm">


                        {{-- ID --}}
                        <div>

                            <dt class="text-xs font-medium text-muted">
                                ID
                            </dt>

                            <dd class="mt-1 font-medium text-ink">
                                {{ $sistema->id }}
                            </dd>

                        </div>


                        {{-- CREADO --}}
                        <div>

                            <dt class="text-xs font-medium text-muted">
                                Creado
                            </dt>

                            <dd class="mt-1 text-ink">

                                {{ $sistema->created_at ? $sistema->created_at->format('d/m/Y H:i') : '—' }}

                            </dd>

                        </div>


                        {{-- ACTUALIZADO --}}
                        <div>

                            <dt class="text-xs font-medium text-muted">
                                Última actualización
                            </dt>

                            <dd class="mt-1 text-ink">

                                {{ $sistema->updated_at ? $sistema->updated_at->format('d/m/Y H:i') : '—' }}

                            </dd>

                        </div>

                    </dl>

                </section>

            </aside>

        </div>


        {{-- ================================================= --}}
        {{-- FOOTER --}}
        {{-- ================================================= --}}

        <footer
            class="mt-4 flex flex-col-reverse gap-3 rounded-xl border border-line bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">

            <a href="{{ route('software.sistemas') }}"
                class="text-center text-sm font-semibold text-muted transition hover:text-ink">
                Cancelar
            </a>


            <button type="submit"
                class="rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                Guardar cambios
            </button>

        </footer>

    </form>


    {{-- ================================================= --}}
    {{-- DATOS DE ACCESOS --}}
    {{-- ================================================= --}}

    <script
        type="application/json"
        id="departamentos-accesos-data"
    >
        @json($departamentos ?? [])
    </script>


    {{-- ================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {


            /*
            |--------------------------------------------------------------------------
            | CONFIGURAR DATALIST DE RESPONSABLES
            |--------------------------------------------------------------------------
            */

            function configurarResponsable(
                inputId,
                hiddenId,
                datalistId
            ) {

                const input =
                    document.getElementById(inputId);

                const hidden =
                    document.getElementById(hiddenId);

                const datalist =
                    document.getElementById(datalistId);


                if (!input || !hidden || !datalist) {
                    return;
                }


                function actualizarResponsable() {

                    const option = Array
                        .from(datalist.options)
                        .find(function(item) {

                            return item.value === input.value;

                        });


                    hidden.value = option ?
                        option.dataset.id :
                        '';

                }


                input.addEventListener(
                    'input',
                    actualizarResponsable
                );


                input.addEventListener(
                    'change',
                    actualizarResponsable
                );

            }



            /*
            |--------------------------------------------------------------------------
            | RESPONSABLE FUNCIONAL
            |--------------------------------------------------------------------------
            */

            configurarResponsable(
                'responsable-funcional',
                'responsable-funcional-id',
                'responsables-funcionales'
            );



            /*
            |--------------------------------------------------------------------------
            | RESPONSABLE TÉCNICO
            |--------------------------------------------------------------------------
            */

            configurarResponsable(
                'responsable-tecnico',
                'responsable-tecnico-id',
                'responsables-tecnicos'
            );



            /*
            |--------------------------------------------------------------------------
            | DEPENDENCIA Y ÁREA
            |--------------------------------------------------------------------------
            */

            const dependenciaInput =
                document.getElementById(
                    'software-dependencia'
                );


            const areaInput =
                document.getElementById(
                    'software-area'
                );

            const areaField =
                document.getElementById(
                    'software-area-field'
                );


            const areasDatalist =
                document.getElementById(
                    'areas-accesos'
                );


            const departamentosData =
                document.getElementById(
                    'departamentos-accesos-data'
                );


            if (
                !dependenciaInput ||
                !areaInput ||
                !areaField ||
                !areasDatalist ||
                !departamentosData
            ) {
                return;
            }



            let departamentos = [];


            try {

                departamentos = JSON.parse(
                    departamentosData.textContent
                );

            } catch (error) {

                console.error(
                    'No fue posible leer los departamentos de Accesos.'
                );

                return;

            }



            /*
            |--------------------------------------------------------------------------
            | CARGAR ÁREAS DE LA DEPENDENCIA
            |--------------------------------------------------------------------------
            */

            function actualizarAreas(limpiarArea = false) {

                const dependenciaNombre =
                    dependenciaInput.value.trim();


                areasDatalist.innerHTML = '';


                const dependencia =
                    departamentos.find(function(item) {

                        return (
                            item.parent_id === null &&
                            item.nombre === dependenciaNombre
                        );

                    });


                if (!dependencia) {

                    areaField.hidden = true;
                    if (limpiarArea) areaInput.value = '';

                    return;

                }


                const areas =
                    departamentos.filter(function(item) {

                        return (
                            String(item.parent_id) ===
                            String(dependencia.id)
                        );

                    });

                if (!areas.length) {

                    areaField.hidden = true;
                    if (limpiarArea) areaInput.value = '';

                    return;

                }

                areaField.hidden = false;


                areas.forEach(function(area) {

                    const option =
                        document.createElement('option');

                    option.value =
                        area.nombre;

                    areasDatalist.appendChild(
                        option
                    );

                });

            }



            /*
            |--------------------------------------------------------------------------
            | CAMBIO DE DEPENDENCIA
            |--------------------------------------------------------------------------
            */

            dependenciaInput.addEventListener(
                'input',
                function() {

                    actualizarAreas(true);

                }
            );



            /*
            |--------------------------------------------------------------------------
            | IMPORTANTE EN EDICIÓN
            |--------------------------------------------------------------------------
            |
            | Al entrar al formulario ya existe una dependencia guardada.
            | Por eso debemos cargar automáticamente sus áreas.
            |
            */

            actualizarAreas();

        });
    </script>

@endsection
