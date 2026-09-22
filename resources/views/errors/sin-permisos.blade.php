@extends('layouts.auth')

@section('title', 'Sin permisos | SICAM')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-6 py-12">
        <section class="w-full max-w-lg rounded-2xl border border-line bg-surface p-8 text-center shadow-sm">
            <img src="{{ asset('assets/icons/sicam-logo.png') }}" alt="SICAM" class="mx-auto mb-6 h-24 object-contain">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand">Acceso restringido</p>
            <h1 class="mt-2 text-2xl font-bold text-ink">Tu cuenta no tiene permisos asignados</h1>
            <p class="mt-3 text-sm leading-6 text-muted">
                Solicita al administrador de Accesos que te asigne al menos un permiso para SICAM y vuelve a iniciar sesión.
            </p>
            <form method="POST" action="{{ route('logout') }}" class="mt-7">
                @csrf
                <button type="submit" class="btn-primary w-full">Cerrar sesión</button>
            </form>
        </section>
    </div>
@endsection
