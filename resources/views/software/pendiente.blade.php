@extends('layouts.app')

@section('title', 'SICAM | Software | '.$titulo)
@section('breadcrumb', 'Software / '.$titulo)

@section('content')
    <div class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-8">
        <h1 class="text-2xl font-bold tracking-tight sm:text-[26px]">{{ $titulo }}</h1>
        <p class="mt-1 text-xs text-muted sm:text-sm">Módulo de Software</p>

        <div class="panel mt-4">
            <h2>Sección en preparación</h2>
            <p class="mt-2 text-sm text-muted">Las funciones de esta sección estarán disponibles en una próxima etapa del módulo.</p>
        </div>
    </div>
@endsection
