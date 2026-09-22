@php
    $licenciasMenuActivo = request()->routeIs('licencia.*');
    $softwareMenuActivo = request()->routeIs('software.*') && ! $licenciasMenuActivo;
    $patrimonioMenuActivo = request()->routeIs('patrimonio.*');
@endphp

<aside id="main-sidebar"
    @if (request()->routeIs('dashboard')) data-tour="Usa este menú para entrar a los módulos y herramientas que tienes autorizados. Las opciones se muestran de acuerdo con tus permisos." data-tour-title="Navegación principal" data-tour-order="20" @endif
    class="fixed inset-y-0 left-0 z-40 flex w-[260px] -translate-x-full flex-col bg-surface px-4 py-6 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
    <div class="relative flex justify-center border-b border-line pb-5">
        <img src="{{ asset('assets/icons/sicam-logo.png') }}" alt="SICAM" class="h-[124px] w-[344px] object-contain">
        <button type="button" data-sidebar-close class="absolute right-0 top-0 rounded-md p-2 text-muted lg:hidden"
            aria-label="Cerrar navegación">×</button>
    </div>

    <nav class="mt-4 flex-1 space-y-4 overflow-y-auto" aria-label="Navegación principal">
        @can('dashboard-sicam.ver')
            <div>
                <p class="menu-label">General</p>
                <a href="{{ route('dashboard') }}"
                    class="menu-link {{ request()->routeIs('dashboard') ? 'menu-link-active' : '' }}"
                    @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                    <img src="{{ asset('assets/icons/home.svg') }}" alt="" class="menu-icon">Inicio
                </a>
            </div>
        @endcan

        @canany(['patrimonio.ver', 'bienes.ver', 'asignaciones.ver', 'movimientos.ver', 'categorias.ver', 'importaciones.ver', 'software.ver', 'sistemas.ver', 'licencias.ver', 'proveedores.ver'])
            <div>
                <p class="menu-label">Módulos</p>

                @canany(['patrimonio.ver', 'bienes.ver', 'asignaciones.ver', 'movimientos.ver', 'categorias.ver', 'importaciones.ver'])
                    <div class="module-dropdown">
                        <button type="button"
                            class="menu-link menu-link-button group w-full {{ $patrimonioMenuActivo ? 'text-brand font-semibold' : '' }}"
                            data-dropdown-toggle="patrimonio-menu" aria-expanded="{{ $patrimonioMenuActivo ? 'true' : 'false' }}"
                            aria-controls="patrimonio-menu">
                            <img src="{{ asset('assets/icons/building.svg') }}" alt="" class="menu-icon">
                            <span>Patrimonio</span>
                            <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                                class="dropdown-arrow h-4 w-4 opacity-90 {{ $patrimonioMenuActivo ? 'is-open' : '' }}">
                        </button>
                        <div id="patrimonio-menu" class="dropdown-panel {{ $patrimonioMenuActivo ? 'is-open' : '' }}"
                            @unless ($patrimonioMenuActivo) hidden @endunless>
                            @can('patrimonio.ver')
                                <a href="{{ route('patrimonio.resumen') }}" class="dropdown-link {{ request()->routeIs('patrimonio.resumen') ? 'dropdown-link-active' : '' }}">Resumen</a>
                            @endcan
                            @can('bienes.ver')
                                <a href="{{ route('patrimonio.bienes') }}" class="dropdown-link {{ request()->routeIs('patrimonio.bienes*') ? 'dropdown-link-active' : '' }}">Bienes</a>
                            @endcan
                            @can('asignaciones.ver')
                                <a href="{{ route('patrimonio.asignaciones.index') }}" class="dropdown-link {{ request()->routeIs('patrimonio.asignaciones.*') ? 'dropdown-link-active' : '' }}">Asignaciones</a>
                            @endcan
                            @can('movimientos.ver')
                                <a href="{{ route('patrimonio.movimientos.index') }}" class="dropdown-link {{ request()->routeIs('patrimonio.movimientos.*') ? 'dropdown-link-active' : '' }}">Movimientos</a>
                            @endcan
                            @can('categorias.ver')
                                <a href="{{ route('patrimonio.categorias') }}" class="dropdown-link {{ request()->routeIs('patrimonio.categorias*', 'patrimonio.campos.*') ? 'dropdown-link-active' : '' }}">Categorías</a>
                            @endcan
                            @can('importaciones.ver')
                                <a href="{{ route('patrimonio.importaciones.index') }}" class="dropdown-link {{ request()->routeIs('patrimonio.importaciones.*') ? 'dropdown-link-active' : '' }}">Importaciones</a>
                            @endcan
                        </div>
                    </div>
                @endcanany

                @canany(['software.ver', 'sistemas.ver'])
                    <div class="module-dropdown">
                        <button type="button"
                            class="menu-link menu-link-button group w-full {{ $softwareMenuActivo ? 'text-brand font-semibold' : '' }}"
                            data-dropdown-toggle="software-menu" aria-expanded="{{ $softwareMenuActivo ? 'true' : 'false' }}"
                            aria-controls="software-menu">
                            <img src="{{ asset('assets/icons/terminal-square.svg') }}" alt="" class="menu-icon">
                            <span>Software</span>
                            <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                                class="dropdown-arrow h-4 w-4 opacity-90 {{ $softwareMenuActivo ? 'is-open' : '' }}">
                        </button>
                        <div id="software-menu" class="dropdown-panel {{ $softwareMenuActivo ? 'is-open' : '' }}"
                            @unless ($softwareMenuActivo) hidden @endunless>
                            @can('software.ver')
                                <a href="{{ route('software.resumen') }}" class="dropdown-link {{ request()->routeIs('software.resumen') ? 'dropdown-link-active' : '' }}">Resumen</a>
                            @endcan
                            @can('sistemas.ver')
                                <a href="{{ route('software.sistemas') }}" class="dropdown-link {{ request()->routeIs('software.sistemas*') ? 'dropdown-link-active' : '' }}">Sistemas</a>
                            @endcan
                        </div>
                    </div>
                @endcanany

                @canany(['licencias.ver', 'proveedores.ver'])
                    <div class="module-dropdown">
                        <button type="button"
                            class="menu-link menu-link-button group w-full {{ $licenciasMenuActivo ? 'text-brand font-semibold' : '' }}"
                            data-dropdown-toggle="licencias-menu" aria-expanded="{{ $licenciasMenuActivo ? 'true' : 'false' }}"
                            aria-controls="licencias-menu">
                            <img src="{{ asset('assets/icons/file-key.svg') }}" alt="" class="menu-icon">
                            <span>Licencias</span>
                            <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                                class="dropdown-arrow h-4 w-4 opacity-90 {{ $licenciasMenuActivo ? 'is-open' : '' }}">
                        </button>
                        <div id="licencias-menu" class="dropdown-panel {{ $licenciasMenuActivo ? 'is-open' : '' }}"
                            @unless ($licenciasMenuActivo) hidden @endunless>
                            @can('licencias.ver')
                                <a href="{{ route('licencia.resumen') }}" class="dropdown-link {{ request()->routeIs('licencia.resumen') ? 'dropdown-link-active' : '' }}">Resumen</a>
                                <a href="{{ route('licencia.index') }}" class="dropdown-link {{ request()->routeIs('licencia.index', 'licencia.create', 'licencia.editar') ? 'dropdown-link-active' : '' }}">Licencias</a>
                            @endcan
                            @can('proveedores.ver')
                                <a href="{{ route('licencia.proveedores') }}" class="dropdown-link {{ request()->routeIs('licencia.proveedores*') ? 'dropdown-link-active' : '' }}">Proveedores</a>
                            @endcan
                        </div>
                    </div>
                @endcanany
            </div>
        @endcanany

        @can('responsables.ver')
            <div>
                <p class="menu-label">Organización</p>
                <a href="{{ route('patrimonio.responsables') }}"
                    class="menu-link {{ request()->routeIs('patrimonio.responsables*') ? 'menu-link-active' : '' }}"
                    @if (request()->routeIs('patrimonio.responsables*')) aria-current="page" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="menu-icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    Responsables
                </a>
            </div>
        @endcan

        @can('auditoria.ver')
            <div>
                <p class="menu-label">Información</p>
                <a href="{{ route('patrimonio.auditoria') }}" class="menu-link {{ request()->routeIs('patrimonio.auditoria') ? 'menu-link-active' : '' }}">
                    <img src="{{ asset('assets/icons/shield-check.svg') }}" alt="" class="menu-icon">Auditoría
                </a>
            </div>
        @endcan
    </nav>

    <div class="pt-4 text-[10px] leading-4 text-muted">
        <p>SICAM Municipal v2.4</p>
        <p>H. Ayuntamiento Constitucional</p>
    </div>
</aside>
<div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-ink/30 lg:hidden"></div>
