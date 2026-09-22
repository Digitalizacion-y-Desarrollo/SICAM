@extends('layouts.app')

@section('title', 'SICAM | Sistemas')

@section('breadcrumb', 'Software / Sistemas')

@section('content')

    {{-- ========================================================= --}}
    {{-- DATATABLE CSS --}}
    {{-- ========================================================= --}}

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">


    <div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">

        {{-- ========================================================= --}}
        {{-- ENCABEZADO --}}
        {{-- ========================================================= --}}

        <div class="flex flex-wrap items-center justify-between gap-4">

            <div>

                <h1 class="text-2xl font-bold">
                    Sistemas
                </h1>

                <p class="mt-1 text-sm text-muted">
                    Inventario de sistemas, aplicaciones y servicios del Ayuntamiento.
                </p>

            </div>


            @can('sistemas.crear')
            <a href="{{ route('software.sistemas.create') }}" class="action-button action-button-primary" data-crud>
                + Registrar sistema
            </a>
            @endcan

        </div>


        {{-- ========================================================= --}}
        {{-- MÉTRICAS --}}
        {{-- ========================================================= --}}

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Totales del inventario">

            @foreach ([
            'Total de sistemas' => $conteos->sum(),
            'En producción' => $conteos->get('produccion', 0),
            'En desarrollo' => $conteos->get('desarrollo', 0),
            'En mantenimiento' => $conteos->get('mantenimiento', 0),
        ] as $etiqueta => $total)
                <div class="metric-card">

                    <p>
                        {{ $etiqueta }}
                    </p>

                    <strong>
                        {{ $total }}
                    </strong>

                </div>
            @endforeach

        </div>


        {{-- ========================================================= --}}
        {{-- FILTROS --}}
        {{-- ========================================================= --}}

        <section class="form-card" aria-label="Filtros de sistemas">

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">


                {{-- BUSCADOR --}}
                <label>

                    <span class="form-label">
                        Buscar
                    </span>

                    <input type="search" id="filtro-buscar" class="form-control" maxlength="191"
                        placeholder="Nombre o clave del sistema">

                </label>


                {{-- ESTADO --}}
                <label>

                    <span class="form-label">
                        Estado
                    </span>

                    <select id="filtro-estado" class="form-control">

                        <option value="">
                            Todos los estados
                        </option>

                        @foreach (\App\Models\Sistema::ESTADOS as $valor => $nombre)
                            <option value="{{ $nombre }}">
                                {{ $nombre }}
                            </option>
                        @endforeach

                    </select>

                </label>


                {{-- TIPO --}}
                <label>

                    <span class="form-label">
                        Tipo
                    </span>

                    <select id="filtro-tipo" class="form-control">

                        <option value="">
                            Todos los tipos
                        </option>

                        @foreach (\App\Models\Sistema::TIPOS as $valor => $nombre)
                            <option value="{{ $nombre }}">
                                {{ $nombre }}
                            </option>
                        @endforeach

                    </select>

                </label>


                {{-- DEPENDENCIA --}}
                <label>

                    <span class="form-label">
                        Dependencia
                    </span>

                    <select id="filtro-dependencia" class="form-control">

                        <option value="">
                            Todas las dependencias
                        </option>

                        @foreach ($dependencias as $dependencia)
                            <option value="{{ $dependencia }}">
                                {{ $dependencia }}
                            </option>
                        @endforeach

                    </select>

                </label>

            </div>


            <div class="mt-4 flex flex-wrap items-center gap-3">

                <button type="button" id="btn-limpiar-filtros" class="action-button">
                    Limpiar filtros
                </button>

                <p id="total-resultados" class="text-xs text-muted sm:ml-auto">
                    {{ $sistemas->count() }} resultados
                </p>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- TABLA --}}
        {{-- ========================================================= --}}

        <section class="overflow-hidden rounded-xl border border-line bg-surface" aria-label="Sistemas registrados">

            <div class="overflow-x-auto">

                <table id="tabla-sistemas" class="asset-table w-full min-w-[850px]">

                    <caption class="sr-only">
                        Inventario de sistemas institucionales
                    </caption>


                    <thead>

                        <tr>

                            <th scope="col">
                                Sistema / clave
                            </th>

                            <th scope="col">
                                Tipo
                            </th>

                            <th scope="col">
                                Dependencia / área
                            </th>

                            <th scope="col">
                                Responsables
                            </th>

                            <th scope="col">
                                Estado
                            </th>

                            <th scope="col">
                                Actualización
                            </th>

                            <th scope="col">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach ($sistemas as $sistema)
                            <tr>

                                {{-- SISTEMA --}}
                                <td class="max-w-64 break-words">

                                    <a href="{{ route('software.sistemas.show', $sistema) }}"
                                        class="cursor-pointer text-left font-semibold text-brand hover:underline">
                                        {{ $sistema->nombre }}
                                    </a>


                                    <p class="mt-1 text-xs text-muted">
                                        {{ $sistema->clave }}
                                    </p>

                                </td>


                                {{-- TIPO --}}
                                <td>

                                    {{ \App\Models\Sistema::TIPOS[$sistema->tipo] ?? $sistema->tipo }}

                                </td>


                                {{-- DEPENDENCIA / ÁREA --}}
                                <td class="max-w-56 break-words">

                                    {{ $sistema->dependencia_id_accesos }}

                                    <p class="mt-1 text-muted">

                                        {{ $sistema->area_id_accesos ?: 'Sin área asignada' }}

                                    </p>

                                </td>


                                {{-- RESPONSABLES --}}
                                <td>

                                    <p>

                                        Funcional:

                                        {{ $sistema->responsableFuncional?->nombre_completo ?: 'Sin asignar' }}

                                    </p>

                                    <p class="mt-1 text-muted">

                                        Técnico:

                                        {{ $sistema->responsableTecnico?->nombre_completo ?: 'Sin asignar' }}

                                    </p>

                                </td>


                                {{-- ESTADO --}}
                                <td>

                                    {{ \App\Models\Sistema::ESTADOS[$sistema->estado] ?? $sistema->estado }}

                                </td>


                                {{-- ACTUALIZACIÓN --}}
                                <td class="whitespace-nowrap" data-order="{{ $sistema->updated_at?->timestamp }}">

                                    {{ $sistema->updated_at?->format('d/m/Y') ?? '—' }}

                                </td>


                                {{-- ACCIONES --}}
                                <td>

                                    <div class="flex items-center gap-2.5">


                                        {{-- VER --}}
                                        <a href="{{ route('software.sistemas.show', $sistema) }}"
                                            class="cursor-pointer font-semibold text-brand hover:underline"
                                            title="Ver sistema" aria-label="Ver {{ $sistema->nombre }}">

                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />

                                            </svg>

                                        </a>


                                        {{-- EDITAR --}}
                                        @can('sistemas.editar')
                                        <a class="font-semibold text-blue-600 hover:underline"
                                            href="{{ route('software.sistemas.edit', $sistema) }}" title="Editar Sistema"
                                            aria-label="Editar {{ $sistema->nombre }}">

                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />

                                            </svg>

                                        </a>
                                        @endcan


                                        {{-- ELIMINAR --}}
                                        @can('sistemas.eliminar')
                                        <form id="delete-sistema-{{ $sistema->id }}"
                                            action="{{ route('software.sistemas.destroy', $sistema) }}" method="POST"
                                            class="inline">

                                            @csrf
                                            @method('DELETE')


                                            <button type="button"
                                                class="cursor-pointer font-semibold text-brand hover:underline"
                                                title="Eliminar sistema" aria-label="Eliminar {{ $sistema->nombre }}"
                                                onclick="confirmarEliminacion(
                                                    {{ $sistema->id }},
                                                    @js($sistema->nombre)
                                                )">

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="size-5">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />

                                                </svg>

                                            </button>

                                        </form>
                                        @endcan

                                    </div>

                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

        </section>

    </div>





    {{-- ========================================================= --}}
    {{-- SCRIPTS --}}
    {{-- ========================================================= --}}

    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>




    <script>
        document.addEventListener('DOMContentLoaded', function() {


            /*
            |--------------------------------------------------------------------------
            | DATATABLE
            |--------------------------------------------------------------------------
            */

            const tablaElemento =
                document.getElementById('tabla-sistemas');


            let tabla = null;


            if (
                tablaElemento &&
                typeof DataTable !== 'undefined'
            ) {

                tabla = new DataTable(
                    '#tabla-sistemas', {

                        pageLength: 10,

                        lengthMenu: [
                            10,
                            25,
                            50,
                            100
                        ],

                        order: [
                            [5, 'desc']
                        ],

                        columnDefs: [

                            {
                                targets: 6,
                                orderable: false,
                                searchable: false
                            }

                        ],

                        layout: {

                            topStart: 'pageLength',

                            topEnd: null,

                            bottomStart: 'info',

                            bottomEnd: 'paging'

                        },

                        language: {

                            emptyTable: 'No hay sistemas registrados.',

                            info: 'Mostrando _START_ a _END_ de _TOTAL_ sistemas',

                            infoEmpty: 'No hay sistemas registrados',

                            infoFiltered: '(filtrado de _MAX_ registros)',

                            lengthMenu: 'Mostrar _MENU_ registros',

                            loadingRecords: 'Cargando...',

                            processing: 'Procesando...',

                            zeroRecords: 'No se encontraron sistemas',

                            paginate: {

                                first: 'Primero',

                                last: 'Último',

                                next: 'Siguiente',

                                previous: 'Anterior'

                            }

                        }

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | FILTROS PERSONALIZADOS
            |--------------------------------------------------------------------------
            */

            const buscar =
                document.getElementById(
                    'filtro-buscar'
                );

            const estado =
                document.getElementById(
                    'filtro-estado'
                );

            const tipo =
                document.getElementById(
                    'filtro-tipo'
                );

            const dependencia =
                document.getElementById(
                    'filtro-dependencia'
                );

            const limpiar =
                document.getElementById(
                    'btn-limpiar-filtros'
                );

            const totalResultados =
                document.getElementById(
                    'total-resultados'
                );


            function actualizarTotal() {

                if (!tabla || !totalResultados) {
                    return;
                }

                const total =
                    tabla
                    .rows({
                        search: 'applied'
                    })
                    .count();


                totalResultados.textContent =
                    `${total} resultados`;

            }


            if (tabla) {


                /*
                | Buscar en toda la tabla
                */

                buscar?.addEventListener(
                    'input',
                    function() {

                        tabla
                            .search(this.value)
                            .draw();

                        actualizarTotal();

                    }
                );


                /*
                | Estado = columna 4
                */

                estado?.addEventListener(
                    'change',
                    function() {

                        tabla
                            .column(4)
                            .search(
                                this.value, {
                                    exact: true
                                }
                            )
                            .draw();

                        actualizarTotal();

                    }
                );


                /*
                | Tipo = columna 1
                */

                tipo?.addEventListener(
                    'change',
                    function() {

                        tabla
                            .column(1)
                            .search(
                                this.value, {
                                    exact: true
                                }
                            )
                            .draw();

                        actualizarTotal();

                    }
                );


                /*
                | Dependencia = columna 2
                |
                | No usamos exact porque la misma celda
                | contiene también el área.
                */

                dependencia?.addEventListener(
                    'change',
                    function() {

                        tabla
                            .column(2)
                            .search(this.value)
                            .draw();

                        actualizarTotal();

                    }
                );


                /*
                | Limpiar filtros
                */

                limpiar?.addEventListener(
                    'click',
                    function() {

                        if (buscar) {
                            buscar.value = '';
                        }

                        if (estado) {
                            estado.value = '';
                        }

                        if (tipo) {
                            tipo.value = '';
                        }

                        if (dependencia) {
                            dependencia.value = '';
                        }


                        tabla.search('');

                        tabla
                            .columns()
                            .search('');

                        tabla.draw();

                        actualizarTotal();

                    }
                );


                actualizarTotal();

            }

        });



        /*
        |--------------------------------------------------------------------------
        | UTILIDAD PARA ASIGNAR TEXTO
        |--------------------------------------------------------------------------
        */

        function asignarTexto(
            id,
            valor,
            fallback = '—'
        ) {

            const elemento =
                document.getElementById(id);


            if (!elemento) {

                console.warn(
                    `No existe #${id}`
                );

                return;

            }


            elemento.textContent =
                valor !== null &&
                valor !== undefined &&
                valor !== '' ?
                valor :
                fallback;

        }



        /*
        |--------------------------------------------------------------------------
        | CONFIGURAR ENLACES
        |--------------------------------------------------------------------------
        */

        function configurarEnlace(
            id,
            url,
            fallback
        ) {

            const elemento =
                document.getElementById(id);


            if (!elemento) {

                console.warn(
                    `No existe #${id}`
                );

                return;

            }


            if (url) {

                elemento.textContent =
                    url;

                elemento.href =
                    url;

                elemento.classList.remove(
                    'pointer-events-none',
                    'text-muted'
                );

                elemento.classList.add(
                    'text-brand'
                );

            } else {

                elemento.textContent =
                    fallback;

                elemento.removeAttribute(
                    'href'
                );

                elemento.classList.add(
                    'pointer-events-none',
                    'text-muted'
                );

                elemento.classList.remove(
                    'text-brand'
                );

            }

        }



        /*
        |--------------------------------------------------------------------------
        | ABRIR MODAL
        |--------------------------------------------------------------------------
        */

        function abrirModalSistema(sistema) {

            const modal =
                document.getElementById(
                    'modal-sistema'
                );


            if (!modal) {

                console.error(
                    'No existe #modal-sistema'
                );

                return;

            }


            /*
            | Header
            */

            asignarTexto(
                'modal-sistema-titulo',
                sistema.nombre
            );

            asignarTexto(
                'modal-sistema-clave',
                sistema.clave
            );


            /*
            | Información general
            */

            asignarTexto(
                'detalle-clave',
                sistema.clave
            );

            asignarTexto(
                'detalle-tipo',
                sistema.tipo
            );

            asignarTexto(
                'detalle-origen',
                sistema.origen
            );

            asignarTexto(
                'detalle-estado',
                sistema.estado
            );

            asignarTexto(
                'detalle-descripcion',
                sistema.descripcion
            );

            asignarTexto(
                'detalle-objetivo',
                sistema.objetivo,
                'Sin objetivo registrado'
            );


            /*
            | Ubicación
            */

            asignarTexto(
                'detalle-dependencia',
                sistema.dependencia
            );

            asignarTexto(
                'detalle-area',
                sistema.area,
                'Sin área asignada'
            );


            /*
            | Responsables
            */

            asignarTexto(
                'detalle-responsable-funcional',
                sistema.responsable_funcional,
                'Sin responsable asignado'
            );

            asignarTexto(
                'detalle-responsable-tecnico',
                sistema.responsable_tecnico,
                'Sin responsable asignado'
            );


            /*
            | Fechas
            */

            asignarTexto(
                'detalle-fecha-inicio',
                sistema.fecha_inicio,
                'Sin fecha registrada'
            );

            asignarTexto(
                'detalle-fecha-liberacion',
                sistema.fecha_liberacion,
                'Sin fecha registrada'
            );

            asignarTexto(
                'detalle-created-at',
                sistema.created_at
            );

            asignarTexto(
                'detalle-updated-at',
                sistema.updated_at
            );


            /*
            | Links
            */

            configurarEnlace(
                'detalle-url-produccion',
                sistema.url_produccion,
                'Sin URL de producción'
            );

            configurarEnlace(
                'detalle-repositorio',
                sistema.repositorio_url,
                'Sin repositorio registrado'
            );


            /*
            | Mostrar modal
            */

            modal.classList.remove(
                'hidden'
            );

            modal.classList.add(
                'flex'
            );


            modal.setAttribute(
                'aria-hidden',
                'false'
            );


            document.body.classList.add(
                'overflow-hidden'
            );

        }



        /*
        |--------------------------------------------------------------------------
        | CERRAR MODAL
        |--------------------------------------------------------------------------
        */

        function cerrarModalSistema() {

            const modal =
                document.getElementById(
                    'modal-sistema'
                );


            if (!modal) {
                return;
            }


            modal.classList.add(
                'hidden'
            );

            modal.classList.remove(
                'flex'
            );


            modal.setAttribute(
                'aria-hidden',
                'true'
            );


            document.body.classList.remove(
                'overflow-hidden'
            );

        }



        /*
        |--------------------------------------------------------------------------
        | CERRAR AL HACER CLICK EN BACKDROP
        |--------------------------------------------------------------------------
        */

        document
            .getElementById('modal-sistema')
            ?.addEventListener(
                'click',
                function(event) {

                    if (
                        event.target === this
                    ) {

                        cerrarModalSistema();

                    }

                }
            );



        /*
        |--------------------------------------------------------------------------
        | CERRAR CON ESC
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function(event) {

                if (
                    event.key === 'Escape'
                ) {

                    cerrarModalSistema();

                }

            }
        );



        /*
        |--------------------------------------------------------------------------
        | SWEET ALERT - ELIMINAR
        |--------------------------------------------------------------------------
        */

        function confirmarEliminacion(
            id,
            nombre
        ) {

            Swal.fire({

                title: '¿Eliminar sistema?',

                text: `¿Estás seguro de eliminar "${nombre}"?`,

                icon: 'warning',

                showCancelButton: true,

                confirmButtonText: 'Sí, eliminar',

                cancelButtonText: 'Cancelar',

                confirmButtonColor: '#601633',

                reverseButtons: true,

                focusCancel: true

            }).then(
                (result) => {

                    if (
                        result.isConfirmed
                    ) {

                        const formulario =
                            document.getElementById(
                                `delete-sistema-${id}`
                            );


                        if (formulario) {

                            formulario.submit();

                        }

                    }

                }
            );

        }
    </script>



@endsection
