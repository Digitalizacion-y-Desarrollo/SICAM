@extends('layouts.auth')

@section('title', 'Acceso denegado | SICAM')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-6 py-12">
        <section class="w-full max-w-lg rounded-2xl border border-line bg-surface p-8 text-center shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand">Error 403</p>
            <h1 class="mt-2 text-2xl font-bold text-ink">No tienes permiso para realizar esta acción</h1>
            <p class="mt-3 text-sm leading-6 text-muted">
                Los permisos de tu cuenta se administran desde Accesos.
            </p>
            <a href="{{ url()->previous() === url()->current() ? route(\App\Support\PermisosAccesos::rutaInicial()) : url()->previous() }}"
                class="btn-primary mt-7 inline-flex">Regresar</a>
        </section>
    </div>
@endsection
