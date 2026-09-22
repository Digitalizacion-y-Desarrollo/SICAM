@extends('layouts.app')

@section('title', 'SICAM | Ver software')

@section('breadcrumb', 'Software / Sistemas / Detalle')

@section('content')

    <div class="mx-auto max-w-[1440px] space-y-5 p-4 sm:p-6 lg:p-8">

        {{-- ========================================================= --}}
        {{-- ENCABEZADO --}}
        {{-- ========================================================= --}}

        <div class="flex flex-wrap items-start justify-between gap-4">

            <div class="min-w-0">

                <div class="flex flex-wrap items-center gap-2">

                    <a
                        href="{{ route('software.sistemas') }}"
                        class="inline-flex items-center gap-2 text-sm font-medium text-muted transition hover:text-brand"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
                            />
                        </svg>

                        Volver a sistemas
                    </a>

                </div>

                <h1 class="mt-3 break-words text-2xl font-bold text-ink sm:text-3xl">
                    {{ $sistema->nombre }}
                </h1>

                <div class="mt-2 flex flex-wrap items-center gap-2">

                    <span
                        class="inline-flex items-center rounded-full bg-soft px-3 py-1 text-xs font-semibold text-muted"
                    >
                        {{ $sistema->clave }}
                    </span>

                    <span
                        class="inline-flex items-center rounded-full bg-soft px-3 py-1 text-xs font-semibold text-ink"
                    >
                        {{ \App\Models\Sistema::TIPOS[$sistema->tipo] ?? $sistema->tipo }}
                    </span>

                    <span
                        class="inline-flex items-center rounded-full bg-brand/10 px-3 py-1 text-xs font-semibold text-brand"
                    >
                        {{ \App\Models\Sistema::ESTADOS[$sistema->estado] ?? $sistema->estado }}
                    </span>

                </div>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                @can('sistemas.editar')
                <a
                    href="{{ route('software.sistemas.edit', $sistema) }}"
                    class="action-button"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"
                        />
                    </svg>

                    Editar sistema
                </a>
                @endcan

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RESUMEN --}}
        {{-- ========================================================= --}}

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

            <div class="metric-card">

                <p>
                    Estado
                </p>

                <strong class="text-base">
                    {{ \App\Models\Sistema::ESTADOS[$sistema->estado] ?? $sistema->estado }}
                </strong>

            </div>

            <div class="metric-card">

                <p>
                    Tipo
                </p>

                <strong class="text-base">
                    {{ \App\Models\Sistema::TIPOS[$sistema->tipo] ?? $sistema->tipo }}
                </strong>

            </div>

            <div class="metric-card">

                <p>
                    Origen
                </p>

                <strong class="text-base">
                    {{ \App\Models\Sistema::ORIGENES[$sistema->origen] ?? $sistema->origen }}
                </strong>

            </div>

            <div class="metric-card">

                <p>
                    Última actualización
                </p>

                <strong class="text-base">
                    {{ $sistema->updated_at?->format('d/m/Y') ?? '—' }}
                </strong>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- INFORMACIÓN PRINCIPAL --}}
        {{-- ========================================================= --}}

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">

            {{-- ===================================================== --}}
            {{-- COLUMNA PRINCIPAL --}}
            {{-- ===================================================== --}}

            <div class="space-y-5">

                {{-- INFORMACIÓN GENERAL --}}
                <section class="form-card">

                    <div class="flex items-center justify-between gap-4 border-b border-line pb-4">

                        <div>

                            <h2 class="text-base font-semibold text-ink">
                                Información general
                            </h2>

                            <p class="mt-1 text-sm text-muted">
                                Datos principales del sistema institucional.
                            </p>

                        </div>

                    </div>


                    <div class="mt-5 grid gap-5 sm:grid-cols-2">

                        <div>

                            <p class="form-label">
                                Nombre
                            </p>

                            <p class="mt-1 break-words text-sm font-medium text-ink">
                                {{ $sistema->nombre }}
                            </p>

                        </div>

                        <div>

                            <p class="form-label">
                                Clave
                            </p>

                            <p class="mt-1 break-words text-sm text-ink">
                                {{ $sistema->clave }}
                            </p>

                        </div>

                        <div>

                            <p class="form-label">
                                Tipo
                            </p>

                            <p class="mt-1 break-words text-sm text-ink">
                                {{ \App\Models\Sistema::TIPOS[$sistema->tipo] ?? $sistema->tipo }}
                            </p>

                        </div>

                        <div>

                            <p class="form-label">
                                Origen
                            </p>

                            <p class="mt-1 break-words text-sm text-ink">
                                {{ \App\Models\Sistema::ORIGENES[$sistema->origen] ?? $sistema->origen }}
                            </p>

                        </div>

                        <div>

                            <p class="form-label">
                                Estado
                            </p>

                            <p class="mt-1 break-words text-sm text-ink">
                                {{ \App\Models\Sistema::ESTADOS[$sistema->estado] ?? $sistema->estado }}
                            </p>

                        </div>

                    </div>


                    <div class="mt-6">

                        <p class="form-label">
                            Descripción
                        </p>

                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-ink">
                            {{ $sistema->descripcion ?: 'Sin descripción registrada.' }}
                        </p>

                    </div>


                    <div class="mt-6">

                        <p class="form-label">
                            Objetivo
                        </p>

                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-ink">
                            {{ $sistema->objetivo ?: 'Sin objetivo registrado.' }}
                        </p>

                    </div>

                </section>


                {{-- UBICACIÓN Y RESPONSABLES --}}
                <div class="grid gap-5 lg:grid-cols-2">

                    {{-- UBICACIÓN --}}
                    <section class="form-card">

                        <div class="border-b border-line pb-4">

                            <h2 class="text-base font-semibold text-ink">
                                Ubicación administrativa
                            </h2>

                            <p class="mt-1 text-sm text-muted">
                                Dependencia y área responsables.
                            </p>

                        </div>


                        <dl class="mt-5 space-y-5">

                            <div>

                                <dt class="form-label">
                                    Dependencia
                                </dt>

                                <dd class="mt-1 break-words text-sm text-ink">
                                    {{ $sistema->dependencia_id_accesos ?: 'Sin dependencia asignada' }}
                                </dd>

                            </div>

                            <div>

                                <dt class="form-label">
                                    Área
                                </dt>

                                <dd class="mt-1 break-words text-sm text-ink">
                                    {{ $sistema->area_id_accesos ?: 'Sin área asignada' }}
                                </dd>

                            </div>

                        </dl>

                    </section>


                    {{-- RESPONSABLES --}}
                    <section class="form-card">

                        <div class="border-b border-line pb-4">

                            <h2 class="text-base font-semibold text-ink">
                                Responsables
                            </h2>

                            <p class="mt-1 text-sm text-muted">
                                Responsables funcional y técnico.
                            </p>

                        </div>


                        <dl class="mt-5 space-y-5">

                            <div>

                                <dt class="form-label">
                                    Responsable funcional
                                </dt>

                                <dd class="mt-1 text-sm text-ink">
                                    {{ $sistema->responsableFuncional?->nombre_completo ?: 'Sin responsable asignado' }}
                                </dd>

                                @if ($sistema->responsableFuncional?->cargo)

                                    <dd class="mt-1 text-xs text-muted">
                                        {{ $sistema->responsableFuncional->cargo }}
                                    </dd>

                                @endif

                            </div>


                            <div>

                                <dt class="form-label">
                                    Responsable técnico
                                </dt>

                                <dd class="mt-1 text-sm text-ink">
                                    {{ $sistema->responsableTecnico?->nombre_completo ?: 'Sin responsable asignado' }}
                                </dd>

                                @if ($sistema->responsableTecnico?->cargo)

                                    <dd class="mt-1 text-xs text-muted">
                                        {{ $sistema->responsableTecnico->cargo }}
                                    </dd>

                                @endif

                            </div>

                        </dl>

                    </section>

                </div>


                {{-- URL Y REPOSITORIO --}}
                <section class="form-card">

                    <div class="border-b border-line pb-4">

                        <h2 class="text-base font-semibold text-ink">
                            Acceso y repositorio
                        </h2>

                        <p class="mt-1 text-sm text-muted">
                            Enlaces relacionados con el sistema.
                        </p>

                    </div>


                    <div class="mt-5 grid gap-5 lg:grid-cols-2">

                        <div class="min-w-0">

                            <p class="form-label">
                                URL de producción
                            </p>

                            @if ($sistema->url_produccion)

                                <a
                                    href="{{ $sistema->url_produccion }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-flex max-w-full items-center gap-2 break-all text-sm font-semibold text-brand hover:underline"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="size-4 shrink-0"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 10.5V18a2.25 2.25 0 0 1-2.25 2.25h-6A2.25 2.25 0 0 1 4.5 18v-6A2.25 2.25 0 0 1 6.75 9.75h7.5"
                                        />
                                    </svg>

                                    {{ $sistema->url_produccion }}
                                </a>

                            @else

                                <p class="mt-2 text-sm text-muted">
                                    Sin URL de producción
                                </p>

                            @endif

                        </div>


                        <div class="min-w-0">

                            <p class="form-label">
                                Repositorio
                            </p>

                            @if ($sistema->repositorio_url)

                                <a
                                    href="{{ $sistema->repositorio_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-flex max-w-full items-center gap-2 break-all text-sm font-semibold text-brand hover:underline"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="size-4 shrink-0"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 10.5V18a2.25 2.25 0 0 1-2.25 2.25h-6A2.25 2.25 0 0 1 4.5 18v-6A2.25 2.25 0 0 1 6.75 9.75h7.5"
                                        />
                                    </svg>

                                    {{ $sistema->repositorio_url }}
                                </a>

                            @else

                                <p class="mt-2 text-sm text-muted">
                                    Sin repositorio registrado
                                </p>

                            @endif

                        </div>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- HISTORIAL --}}
                {{-- ================================================= --}}

                <section class="overflow-hidden rounded-xl border border-line bg-surface">

                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">

                        <div>

                            <h2 class="text-base font-semibold text-ink">
                                Últimas actualizaciones
                            </h2>

                            <p class="mt-1 text-sm text-muted">
                                Historial reciente de cambios realizados sobre el sistema.
                            </p>

                        </div>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="asset-table w-full min-w-[850px]">

                            <thead>

                                <tr>

                                    <th>
                                        Fecha
                                    </th>

                                    <th>
                                        Acción
                                    </th>

                                    <th>
                                        Campo
                                    </th>

                                    <th>
                                        Valor anterior
                                    </th>

                                    <th>
                                        Valor nuevo
                                    </th>

                                    <th>
                                        Usuario
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse ($historial ?? [] as $registro)

                                    <tr>

                                        <td class="whitespace-nowrap">

                                            {{ $registro->created_at?->format('d/m/Y H:i') ?? '—' }}

                                        </td>

                                        <td>

                                            {{ $registro->accion ?? 'Actualización' }}

                                        </td>

                                        <td>

                                            {{ $registro->campo ?? '—' }}

                                        </td>

                                        <td class="max-w-64 break-words text-muted">

                                            {{ $registro->valor_anterior ?? '—' }}

                                        </td>

                                        <td class="max-w-64 break-words">

                                            {{ $registro->valor_nuevo ?? '—' }}

                                        </td>

                                        <td>

                                            {{ $registro->usuario_nombre ?? 'Sistema' }}

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6">

                                            <div class="py-12 text-center">

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                    class="mx-auto size-10 text-muted"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                                    />
                                                </svg>

                                                <p class="mt-3 text-sm font-semibold text-ink">
                                                    Sin historial registrado
                                                </p>

                                                <p class="mt-1 text-sm text-muted">
                                                    Las actualizaciones del sistema aparecerán aquí.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </section>

            </div>


            {{-- ===================================================== --}}
            {{-- COLUMNA LATERAL --}}
            {{-- ===================================================== --}}

            <aside class="space-y-5">

                {{-- CICLO DE VIDA --}}
                <section class="form-card">

                    <h2 class="text-base font-semibold text-ink">
                        Ciclo de vida
                    </h2>

                    <dl class="mt-5 space-y-5">

                        <div>

                            <dt class="form-label">
                                Fecha de inicio
                            </dt>

                            <dd class="mt-1 text-sm text-ink">
                                {{ $sistema->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha registrada' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="form-label">
                                Fecha de liberación
                            </dt>

                            <dd class="mt-1 text-sm text-ink">
                                {{ $sistema->fecha_liberacion?->format('d/m/Y') ?? 'Sin fecha registrada' }}
                            </dd>

                        </div>

                    </dl>

                </section>


                {{-- REGISTRO --}}
                <section class="form-card">

                    <h2 class="text-base font-semibold text-ink">
                        Información del registro
                    </h2>

                    <dl class="mt-5 space-y-5">

                        <div>

                            <dt class="form-label">
                                ID
                            </dt>

                            <dd class="mt-1 text-sm text-ink">
                                #{{ $sistema->id }}
                            </dd>

                        </div>


                        <div>

                            <dt class="form-label">
                                Creado
                            </dt>

                            <dd class="mt-1 text-sm text-ink">
                                {{ $sistema->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="form-label">
                                Última actualización
                            </dt>

                            <dd class="mt-1 text-sm text-ink">
                                {{ $sistema->updated_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>

                        </div>

                    </dl>

                </section>


                {{-- ACCIONES --}}
                <section class="form-card">

                    <h2 class="text-base font-semibold text-ink">
                        Acciones
                    </h2>

                    <div class="mt-4 space-y-2">

                        @can('sistemas.editar')
                        <a
                            href="{{ route('software.sistemas.edit', $sistema) }}"
                            class="action-button action-button-primary flex w-full justify-center"
                        >
                            Editar sistema
                        </a>
                        @endcan


                        <a
                            href="{{ route('software.sistemas') }}"
                            class="action-button flex w-full justify-center"
                        >
                            Volver al listado
                        </a>

                    </div>

                </section>

            </aside>

        </div>

    </div>

@endsection
