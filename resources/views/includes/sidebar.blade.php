@php
    $licenciasMenuActivo = request()->routeIs('licencia.*');
    $softwareMenuActivo = request()->routeIs('software.*') && ! $licenciasMenuActivo;
@endphp

<aside id="main-sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-[260px] -translate-x-full flex-col bg-surface px-4 py-6 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
    <div class="relative flex justify-center border-b border-line pb-5">
        <img src="{{ asset('assets/icons/sicam-logo.png') }}" alt="SICAM" class="h-[124px] w-[344px] object-contain">
        <button type="button" data-sidebar-close class="absolute right-0 top-0 rounded-md p-2 text-muted lg:hidden"
            aria-label="Cerrar navegación">×</button>
    </div>

    <nav class="mt-4 flex-1 space-y-4 overflow-y-auto" aria-label="Navegación principal">
        <div>
            <p class="menu-label">General</p>
            <a href="{{ route('dashboard') }}"
                class="menu-link {{ request()->routeIs('dashboard') ? 'menu-link-active' : '' }}"
                @if (request()->routeIs('dashboard')) aria-current="page" @endif><img
                    src="{{ asset('assets/icons/home.svg') }}" alt="" class="menu-icon">Inicio</a>
        </div>
        <div>
            <p class="menu-label">Módulos</p>
            <div class="module-dropdown">
                <button type="button"
                    class="menu-link menu-link-button group w-full {{ request()->routeIs('patrimonio.*') ? 'text-brand font-semibold' : '' }}"
                    data-dropdown-toggle="patrimonio-menu"
                    aria-expanded="{{ request()->routeIs('patrimonio.*') ? 'true' : 'false' }}"
                    aria-controls="patrimonio-menu">
                    <img src="{{ asset('assets/icons/building.svg') }}" alt="" class="menu-icon">
                    <span>Patrimonio</span>
                    <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                        class="dropdown-arrow h-4 w-4 opacity-90 {{ request()->routeIs('patrimonio.*') ? 'is-open' : '' }}">
                </button>
                <div id="patrimonio-menu"
                    class="dropdown-panel {{ request()->routeIs('patrimonio.*') ? 'is-open' : '' }}"
                    @unless (request()->routeIs('patrimonio.*')) hidden @endunless>
                    <div class="min-h-0">
                        <a href="{{ route('patrimonio.resumen') }}"
                            class="dropdown-link {{ request()->routeIs('patrimonio.resumen') ? 'dropdown-link-active' : '' }}">Resumen</a>
                        <a href="{{ route('patrimonio.bienes') }}"
                            class="dropdown-link {{ request()->routeIs('patrimonio.bienes') ? 'dropdown-link-active' : '' }}">Bienes</a>
                        <a href="{{ route('patrimonio.asignaciones.index') }}"
                            class="dropdown-link {{ request()->routeIs('patrimonio.asignaciones.*') ? 'dropdown-link-active' : '' }}">Asignaciones</a>
                        <a href="{{ route('patrimonio.movimientos.index') }}"
                            class="dropdown-link {{ request()->routeIs('patrimonio.movimientos.*') ? 'dropdown-link-active' : '' }}">Movimientos</a>
                        <a href="{{ route('patrimonio.categorias') }}"
                            class="dropdown-link {{ request()->routeIs('patrimonio.categorias') ? 'dropdown-link-active' : '' }}">Categorías</a>
                        <a href="{{ route('patrimonio.importaciones.index') }}"
                            class="dropdown-link {{ request()->routeIs('patrimonio.importaciones.*') ? 'dropdown-link-active' : '' }}">Importaciones</a>
                    </div>
                </div>
            </div>
            <div class="module-dropdown">
                <button type="button"
                    class="menu-link menu-link-button group w-full {{ $softwareMenuActivo ? 'text-brand font-semibold' : '' }}"
                    data-dropdown-toggle="software-menu"
                    aria-expanded="{{ $softwareMenuActivo ? 'true' : 'false' }}"
                    aria-controls="software-menu">
                    <img src="{{ asset('assets/icons/terminal-square.svg') }}" alt="" class="menu-icon">
                    <span>Software</span>
                    <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                        class="dropdown-arrow h-4 w-4 opacity-90 {{ $softwareMenuActivo ? 'is-open' : '' }}">
                </button>
                <div id="software-menu" class="dropdown-panel {{ $softwareMenuActivo ? 'is-open' : '' }}"
                    @unless ($softwareMenuActivo) hidden @endunless>
                    <div class="min-h-0">
                        <a href="{{ route('software.resumen') }}"
                            class="dropdown-link {{ request()->routeIs('software.resumen') ? 'dropdown-link-active' : '' }}">Resumen</a>
                    </div>
                    <div class="min-h-0">
                        <a href="{{ route('software.sistemas') }}"
                            class="dropdown-link {{ request()->routeIs('software.sistemas') ? 'dropdown-link-active' : '' }}">Sistemas</a>
                    </div>
                </div>
            </div>
            <div class="module-dropdown">
                <button type="button"
                    class="menu-link menu-link-button group w-full {{ $licenciasMenuActivo ? 'text-brand font-semibold' : '' }}"
                    data-dropdown-toggle="licencias-menu"
                    aria-expanded="{{ $licenciasMenuActivo ? 'true' : 'false' }}"
                    aria-controls="licencias-menu">
                    <img src="{{ asset('assets/icons/file-key.svg') }}" alt="" class="menu-icon">
                    <span>Licencias</span>
                    <img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                        class="dropdown-arrow h-4 w-4 opacity-90 {{ $licenciasMenuActivo ? 'is-open' : '' }}">
                </button>
                <div id="licencias-menu" class="dropdown-panel {{ $licenciasMenuActivo ? 'is-open' : '' }}"
                    @unless ($licenciasMenuActivo) hidden @endunless>
                    <div class="min-h-0">
                        <a href="{{ route('licencia.resumen') }}"
                            class="dropdown-link {{ request()->routeIs('licencia.resumen') ? 'dropdown-link-active' : '' }}">Resumen</a>
                    </div>
                    <div class="min-h-0">
                        <a href="{{ route('licencia.index') }}"
                            class="dropdown-link {{ request()->routeIs('licencia.index', 'licencia.create') ? 'dropdown-link-active' : '' }}">Licencias</a>
                    </div>
                    <div class="min-h-0">
                        <a href="{{ route('licencia.proveedores') }}"
                            class="dropdown-link {{ request()->routeIs('licencia.proveedores*') ? 'dropdown-link-active' : '' }}">Proveedores</a>
                    </div>
                </div>
            </div>
            <a href="#" class="menu-link"><img src="{{ asset('assets/icons/chart-network.svg') }}" alt=""
                    class="menu-icon">Redes <span class="menu-badge">Próximamente</span></a>
        </div>
        <div>
            <p class="menu-label">Organización</p>
            <a href="{{ route('patrimonio.responsables') }}"
                class="menu-link {{ request()->routeIs('patrimonio.responsables*') ? 'menu-link-active' : '' }}"
                @if (request()->routeIs('patrimonio.responsables*')) aria-current="page" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="menu-icon">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>Responsables</a>
            <a href="#" class="menu-link"><img src="{{ asset('assets/icons/chart-network.svg') }}" alt=""
                    class="menu-icon">Dependencias</a>
            <a href="#" class="menu-link"><img src="{{ asset('assets/icons/map-pin.svg') }}" alt=""
                    class="menu-icon">Áreas</a>

        </div>
        <div>
            <p class="menu-label">Información</p>
            <a href="#" class="menu-link"><img src="{{ asset('assets/icons/bar-chart.svg') }}" alt=""
                    class="menu-icon">Reportes</a>
            <a href="{{ route('patrimonio.auditoria') }}" class="menu-link"><img
                    src="{{ asset('assets/icons/shield-check.svg') }}" alt=""
                    class="menu-icon">Auditoría</a>
        </div>
        <div>
            <p class="menu-label">Administración</p>
            <a href="#" class="menu-link"><img src="{{ asset('assets/icons/cog.svg') }}" alt=""
                    class="menu-icon">Configuración</a>
        </div>
    </nav>

    <div class="pt-4 text-[10px] leading-4 text-muted">
        <p>SICAM Municipal v2.4</p>
        <p>H. Ayuntamiento Constitucional</p>
    </div>
</aside>
<div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-ink/30 lg:hidden"></div>
