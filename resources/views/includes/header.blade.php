<header class="flex h-16 items-center justify-between border-b border-line bg-surface px-4 sm:px-6">
    <div class="flex items-center gap-3 text-xs text-muted">
        <button type="button" data-sidebar-open class="rounded-md p-2 text-ink lg:hidden" aria-label="Abrir navegación"
            @if (request()->routeIs('dashboard')) data-tour="Abre el menú principal para navegar entre los módulos disponibles." data-tour-title="Menú de navegación" data-tour-order="20" @endif>☰</button>
        <img src="{{ asset('assets/icons/shield-check.svg') }}" alt="" class="hidden size-4 sm:block">
        <span>SICAM / <span class="text-ink">@yield('breadcrumb', 'Inicio')</span></span>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" data-help class="action-button" aria-label="Ayuda de esta sección"
            @if (request()->routeIs('dashboard')) data-tour="Puedes volver a iniciar este recorrido en cualquier momento desde el botón Ayuda." data-tour-title="Ayuda" data-tour-order="90" @endif>Ayuda</button>
        @can('busqueda.global')
            <button type="button" data-global-search-open
                @if (request()->routeIs('dashboard')) data-tour="Busca bienes, sistemas, licencias y responsables desde cualquier sección. También puedes abrir el buscador con Ctrl + K." data-tour-title="Búsqueda global" data-tour-order="30" @endif
                class="flex h-9 items-center gap-2 rounded-lg border border-line bg-surface-alt px-2.5 text-xs text-muted transition hover:border-brand/40 hover:bg-white sm:w-52 lg:w-60"
                aria-label="Abrir búsqueda global" aria-haspopup="dialog">
                <img src="{{ asset('assets/icons/search.svg') }}" alt="" class="size-4">
                <span class="hidden flex-1 text-left sm:block">Buscar en SICAM...</span>
                <kbd class="hidden rounded border border-line bg-white px-1.5 py-0.5 font-sans text-[10px] text-muted sm:inline-flex">Ctrl K</kbd>
            </button>
        @endcan
        <button type="button" class="relative rounded-lg p-2" aria-label="Notificaciones"><img src="{{ asset('assets/icons/bell-dot.svg') }}" alt="" class="size-5"></button>
        <div class="hidden text-right text-xs sm:block"><p class="font-semibold text-ink">{{ auth()->user()?->name ?? 'Sin sesión' }}</p><p class="text-[10px] text-muted">SICAM Municipal</p></div>
        <span class="grid size-8 place-items-center rounded-full bg-brand text-xs font-semibold text-white">{{ auth()->user() ? mb_substr(auth()->user()->name, 0, 1) : '—' }}</span>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="action-button" title="Cerrar sesión" aria-label="Cerrar sesión">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M10 17l5-5-5-5M15 12H3M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </form>
        @endauth
    </div>
</header>

@can('busqueda.global')
    <div data-global-search-dialog data-search-url="{{ route('busqueda.global') }}"
        class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto px-4 py-8 opacity-0 transition-opacity duration-200"
        role="dialog" aria-modal="true" aria-labelledby="global-search-title">
        <button type="button" data-global-search-close
            class="absolute inset-0 cursor-default bg-slate-950/35 backdrop-blur-md"
            aria-label="Cerrar búsqueda"></button>

        <section data-global-search-panel
            class="relative w-full max-w-2xl translate-y-3 overflow-hidden rounded-2xl border border-white/60 bg-white/85 shadow-2xl shadow-slate-950/25 backdrop-blur-2xl transition-transform duration-200">
            <h2 id="global-search-title" class="sr-only">Buscar en SICAM</h2>
            <div class="flex items-center gap-3 border-b border-white/70 px-4 sm:px-5">
                <img src="{{ asset('assets/icons/search.svg') }}" alt="" class="size-5 shrink-0 opacity-70">
                <input type="search" data-global-search-input autocomplete="off" maxlength="80"
                    class="h-16 min-w-0 flex-1 bg-transparent text-base text-ink outline-none placeholder:text-muted"
                    placeholder="Busca bienes, sistemas, licencias, responsables..."
                    aria-describedby="global-search-help" aria-controls="global-search-results">
                <button type="button" data-global-search-close
                    class="rounded-md border border-line bg-white/70 px-2 py-1 text-[11px] font-semibold text-muted hover:text-ink">Esc</button>
            </div>

            <div id="global-search-results" data-global-search-results class="max-h-[55vh] overflow-y-auto p-2 sm:p-3"
                role="listbox" aria-live="polite">
                <div class="px-4 py-10 text-center">
                    <p class="text-sm font-semibold text-ink">Busca en todo SICAM</p>
                    <p id="global-search-help" class="mt-1 text-xs text-muted">Escribe al menos dos caracteres para comenzar.</p>
                </div>
            </div>

            <footer class="flex items-center justify-between border-t border-white/70 bg-white/45 px-4 py-2.5 text-[10px] text-muted sm:px-5">
                <span>↑ ↓ para navegar · Enter para abrir</span>
                <span>Solo se muestran módulos autorizados</span>
            </footer>
        </section>
    </div>
@endcan
