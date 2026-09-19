<header class="flex h-16 items-center justify-between border-b border-line bg-surface px-4 sm:px-6">
    <div class="flex items-center gap-3 text-xs text-muted">
        <button type="button" data-sidebar-open class="rounded-md p-2 text-ink lg:hidden" aria-label="Abrir navegación">☰</button>
        <img src="{{ asset('assets/icons/shield-check.svg') }}" alt="" class="hidden size-4 sm:block">
        <span>SICAM / <span class="text-ink">@yield('breadcrumb', 'Inicio')</span></span>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" data-help class="action-button" aria-label="Ayuda de esta sección">Ayuda</button>
        <label class="hidden h-9 w-60 items-center gap-2 rounded-lg border border-line bg-surface-alt px-3 md:flex">
            <img src="{{ asset('assets/icons/search.svg') }}" alt="" class="size-4"><input type="search" class="w-full bg-transparent text-xs outline-none placeholder:text-muted" placeholder="Buscar en SICAM...">
        </label>
        <button type="button" class="relative rounded-lg p-2" aria-label="Notificaciones"><img src="{{ asset('assets/icons/bell-dot.svg') }}" alt="" class="size-5"></button>
        <div class="hidden text-right text-xs sm:block"><p class="font-semibold text-ink">{{ auth()->user()?->name ?? 'Sin sesión' }}</p><p class="text-[10px] text-muted">SICAM Municipal</p></div>
        <span class="grid size-8 place-items-center rounded-full bg-brand text-xs font-semibold text-white">{{ auth()->user() ? mb_substr(auth()->user()->name, 0, 1) : '—' }}</span>
    </div>
</header>
