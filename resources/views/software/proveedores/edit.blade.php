@extends('layouts.app')

@section('title', 'SICAM | Editar proveedor')
@section('breadcrumb', 'Licencias / Proveedores / Editar')

@section('content')
    <div class="mx-auto max-w-4xl p-4 sm:p-6 lg:p-8">
        <header class="mb-5">
            <p class="section-heading">Módulo de Licencias</p>
            <h1 class="mt-1 text-2xl font-bold text-ink">Editar proveedor</h1>
            <p class="mt-1 text-sm text-muted">Actualiza los datos fiscales y de contacto del proveedor.</p>
        </header>

        <form method="POST" action="{{ route('licencia.proveedores.update', $proveedor) }}" class="space-y-5" novalidate>
            @csrf
            @method('PUT')

            <section class="form-card">
                <h2>Información general</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="form-label">Nombre del proveedor o plataforma *</span>
                        <input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}"
                            class="form-control" maxlength="191" required autofocus>
                        @error('nombre')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="form-label">Razón social</span>
                        <input type="text" name="razon_social" value="{{ old('razon_social', $proveedor->razon_social) }}"
                            class="form-control" maxlength="191">
                        @error('razon_social')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="form-label">RFC</span>
                        <input type="text" name="rfc" value="{{ old('rfc', $proveedor->rfc) }}"
                            class="form-control uppercase" maxlength="20">
                        @error('rfc')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                    <label class="sm:col-span-2">
                        <span class="form-label">Sitio web</span>
                        <input type="url" name="sitio_web" value="{{ old('sitio_web', $proveedor->sitio_web) }}"
                            class="form-control" maxlength="2048" placeholder="https://proveedor.com">
                        @error('sitio_web')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="form-card">
                <h2>Contacto</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="form-label">Persona de contacto</span>
                        <input type="text" name="contacto_nombre"
                            value="{{ old('contacto_nombre', $proveedor->contacto_nombre) }}"
                            class="form-control" maxlength="191">
                        @error('contacto_nombre')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="form-label">Correo de contacto</span>
                        <input type="email" name="contacto_email"
                            value="{{ old('contacto_email', $proveedor->contacto_email) }}"
                            class="form-control" maxlength="191">
                        @error('contacto_email')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="form-label">Teléfono de contacto</span>
                        <input type="text" name="contacto_telefono"
                            value="{{ old('contacto_telefono', $proveedor->contacto_telefono) }}"
                            class="form-control" maxlength="30">
                        @error('contacto_telefono')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            <section class="form-card">
                <h2>Estado y observaciones</h2>
                <input type="hidden" name="activo" value="0">
                <label class="mt-4 flex items-start gap-3 rounded-lg border border-line bg-surface-alt p-3">
                    <input type="checkbox" name="activo" value="1"
                        class="mt-0.5 size-4 rounded border-line text-brand focus:ring-brand"
                        @checked(old('activo', $proveedor->activo))>
                    <span>
                        <span class="block text-sm font-semibold text-ink">Proveedor activo</span>
                        <span class="mt-1 block text-xs text-muted">Los proveedores activos pueden seleccionarse al registrar licencias.</span>
                    </span>
                </label>
                @error('activo')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror

                <label class="mt-4 block">
                    <span class="form-label">Observaciones</span>
                    <textarea name="observaciones" class="form-control h-auto min-h-28 p-3" rows="4"
                        maxlength="5000">{{ old('observaciones', $proveedor->observaciones) }}</textarea>
                    @error('observaciones')<span class="mt-1 block text-xs font-medium text-red-600" role="alert">{{ $message }}</span>@enderror
                </label>
            </section>

            <footer class="flex flex-col-reverse gap-3 rounded-xl border border-line bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('licencia.proveedores') }}"
                    class="text-center text-sm font-semibold text-muted transition hover:text-ink">Cancelar</a>
                <button type="submit" class="action-button action-button-primary">Guardar cambios</button>
            </footer>
        </form>
    </div>
@endsection
