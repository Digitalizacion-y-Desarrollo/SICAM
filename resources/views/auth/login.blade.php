@extends('layouts.auth')

@section('title', 'Iniciar sesión | SICAM')

@section('content')
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(480px,42%)]">
        <section class="relative hidden overflow-hidden bg-brand px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -left-28 top-1/3 size-80 rounded-full border border-white/10"></div>
            <div class="absolute -right-32 -top-32 size-[430px] rounded-full bg-white/[.04]"></div>
            <div class="absolute bottom-[-180px] right-[-80px] size-[460px] rounded-full border-[70px] border-gold/15"></div>

            <div class="relative flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-xl bg-white shadow-sm">
                    <img src="{{ asset('assets/icons/sicam-logo.png') }}" alt="SICAM" class="size-10 object-contain">
                </span>
                <div>
                    <p class="text-xl font-bold tracking-tight">SICAM</p>
                    <p class="text-xs text-white/70">Gobierno Municipal de Nezahualcóyotl</p>
                </div>
            </div>

            <div class="relative max-w-xl">
                <span class="mb-6 grid size-14 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/15">
                    <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-7h6v7M8 10h.01M12 10h.01M16 10h.01" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h1 class="text-4xl font-bold leading-tight tracking-tight xl:text-5xl">Control municipal en un solo lugar</h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/75">Administra el patrimonio, los resguardos y los movimientos de bienes con información clara y trazable.</p>
            </div>

            <p class="relative text-xs text-white/55">Sistema Integral de Control de Activos Municipales</p>
        </section>

        <section class="flex min-h-screen items-center justify-center bg-white px-5 py-10 sm:px-10 lg:px-14">
            <div class="w-full max-w-md">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('assets/icons/sicam-logo.png') }}" alt="SICAM" class="size-11 object-contain">
                    <div><p class="font-bold text-brand">SICAM</p><p class="text-[11px] text-muted">Gobierno Municipal de Nezahualcóyotl</p></div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-gold-ink">Bienvenido</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-ink">Inicia sesión</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">Ingresa tus datos para acceder al sistema.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <label class="block">
                        <span class="form-label">Correo electrónico</span>
                        <div class="relative">
                            <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 6 9 6 9-6M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus required class="form-control pl-10 @error('email') border-red-500 @enderror" placeholder="nombre@nezahualcoyotl.gob.mx" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')<p id="email-error" class="mt-1.5 text-sm text-red-700" data-field-error="email" role="alert">{{ $message }}</p>@enderror
                    </label>

                    <label class="block">
                        <span class="form-label">Contraseña</span>
                        <div class="relative">
                            <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect width="16" height="11" x="4" y="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke-linecap="round"/></svg>
                            <input id="login-password" name="password" type="password" autocomplete="current-password" required class="form-control px-10 @error('password') border-red-500 @enderror" placeholder="Ingresa tu contraseña" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" data-password-toggle="login-password" class="absolute right-2 top-1/2 grid size-8 -translate-y-1/2 place-items-center rounded-md text-muted transition hover:bg-soft hover:text-ink" aria-label="Mostrar contraseña">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </button>
                        </div>
                        @error('password')<p id="password-error" class="mt-1.5 text-sm text-red-700" data-field-error="password" role="alert">{{ $message }}</p>@enderror
                    </label>

                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-muted">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-line text-brand focus:ring-brand">
                        Mantener mi sesión iniciada
                    </label>

                    <button type="submit" class="flex h-11 w-full items-center justify-center rounded-lg bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">Ingresar al sistema</button>
                </form>

                <p class="mt-8 text-center text-xs leading-5 text-muted">Si tienes problemas para ingresar, comunícate con el área de Sistemas.</p>
            </div>
        </section>
    </div>
@endsection
