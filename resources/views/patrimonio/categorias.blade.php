@extends('layouts.app')
@section('title', 'SICAM | Categorías')
@section('content')
    <div class="mx-auto max-w-[1440px] p-4 sm:p-6 lg:px-8 lg:py-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Categorías de bienes</h1>
                <p class="mt-1 text-sm text-muted">Configura los tipos de bienes patrimoniales y sus campos dinámicos</p>
            </div>
            <details class="relative">
                <summary class="action-button action-button-primary list-none cursor-pointer">+ Nueva categoría</summary>
                <form method="POST" action="{{ route('patrimonio.categorias.store') }}"
                    class="absolute right-0 z-20 mt-2 w-80 space-y-3 rounded-xl border border-line bg-white p-4 shadow-xl">
                    @csrf<input name="nombre" class="form-control" placeholder="Nombre de categoría" required><select
                        name="categoria_padre_id" class="form-control">
                        <option value="">Categoría principal</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    <textarea name="descripcion" class="form-control h-20 py-2" placeholder="Descripción"></textarea><button
                        class="action-button action-button-primary w-full justify-center">Guardar categoría</button>
                </form>
            </details>
        </div>
        <div class="mt-5 grid gap-4 xl:grid-cols-[300px_minmax(0,1fr)]">
            <aside class="form-card self-start">
                <h2>Jerarquía de Categorías</h2>
                <div class="mt-3 space-y-2">
                    @forelse($categorias as $categoria)
                        <div><a href="{{ route('patrimonio.categorias', ['categoria' => $categoria]) }}"
                                class="tree-item"><img src="{{ asset('assets/icons/chevron-down.svg') }}" alt=""
                                    class="size-4 shrink-0 opacity-80"><img src="{{ asset('assets/icons/building.svg') }}"
                                    alt="" class="size-4"><span class="flex-1">{{ $categoria->nombre }}</span><small
                                    class="text-[10px] text-muted">{{ $categoria->hijas->count() }} subcat.</small></a>
                            <div class="ml-6 border-l border-line pl-2">
                                @foreach ($categoria->hijas as $hija)
                                    <a href="{{ route('patrimonio.categorias', ['categoria' => $hija]) }}"
                                        class="tree-child {{ $seleccionada?->is($hija) ? 'bg-brand/[.07] font-semibold text-brand' : '' }}">{{ $hija->nombre }}</a>
                                @endforeach
                            </div>
                    </div>@empty<p class="py-6 text-center text-xs text-muted">Aún no hay categorías.</p>
                    @endforelse
                </div>
            </aside>
            <section class="form-card">
                @if ($seleccionada)
                    <div class="flex flex-wrap justify-between gap-3 border-b border-line pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl">{{ $seleccionada->nombre }}</h2>
                                @if ($seleccionada->padre)
                                    <span
                                        class="rounded bg-brand/[.07] px-2 py-1 text-[10px] text-brand">{{ $seleccionada->padre->nombre }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-muted">{{ $seleccionada->bienes_count }} bienes registrados bajo
                                este tipo</p>
                        </div><span
                            class="asset-status flex items-center {{ $seleccionada->activo ? 'asset-status-assigned' : 'asset-status-unassigned' }}">{{ $seleccionada->activo ? 'Categoría activa' : 'Inactiva' }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3"><a data-crud class="action-button" href="{{ route('patrimonio.categorias.edit', $seleccionada) }}">Editar categoría</a><form method="POST" action="{{ route('patrimonio.categorias.destroy', $seleccionada) }}">@csrf @method('DELETE')<button class="action-button text-red-700">Eliminar categoría</button></form></div>
                    <div class="py-4">
                        <p class="text-[11px] font-semibold uppercase text-muted">Descripción</p>
                        <p class="mt-1 text-sm">{{ $seleccionada->descripcion ?: 'Sin descripción.' }}</p>
                    </div>
                    <h3 class="mb-2 text-[11px] font-semibold uppercase text-muted">Campos dinámicos del formulario</h3>
                    <div class="overflow-x-auto rounded-lg border border-line">
                        <table class="data-table w-full min-w-[600px]">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Obligatorio</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($seleccionada->campos as $campo)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="font-semibold">{{ $campo->nombre }} @unless($campo->activo)<span class="text-xs text-muted">(Inactivo)</span>@endunless</td>
                                        <td>{{ str($campo->tipo)->headline() }}</td>
                                        <td>{{ $campo->requerido ? 'Sí' : 'No' }}</td>
                                        <td><div class="flex justify-end gap-3">
                                            <a data-crud href="{{ route('patrimonio.campos.edit', $campo) }}" class="text-brand" title="Editar campo">@include('patrimonio.includes.action-icon', ['icon' => 'edit', 'label' => 'Editar campo'])</a>
                                            <form method="POST"
                                                action="{{ route('patrimonio.categorias.campos.destroy', $campo) }}"
                                                >@csrf @method('DELETE')<button class="text-red-500" title="Eliminar campo">@include('patrimonio.includes.action-icon', ['icon' => 'delete', 'label' => 'Eliminar campo'])</button>
                                            </form>
                                        </div></td>
                                </tr>@empty<tr>
                                        <td colspan="5" class="py-8 text-center text-muted">Esta categoría aún no tiene
                                            campos dinámicos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <details class="mt-4">
                        <summary class="action-button list-none cursor-pointer text-brand">+ Agregar campo dinámico
                        </summary>
                        <form method="POST" action="{{ route('patrimonio.categorias.campos.store', $seleccionada) }}"
                            class="mt-3 grid gap-3 rounded-lg bg-surface-alt p-4 sm:grid-cols-3" data-campo-dinamico-form>@csrf<input name="nombre"
                                class="form-control" placeholder="Nombre del campo" value="{{ old('nombre') }}" required><select name="tipo"
                                class="form-control" data-campo-tipo>
                                <option value="TEXTO" @selected(old('tipo', 'TEXTO') === 'TEXTO')>Texto</option>
                                <option value="NUMERO" @selected(old('tipo') === 'NUMERO')>Número</option>
                                <option value="FECHA" @selected(old('tipo') === 'FECHA')>Fecha</option>
                                <option value="SELECCION" @selected(old('tipo') === 'SELECCION')>Selección</option>
                            </select><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requerido"
                                    value="1" @checked(old('requerido'))> Obligatorio</label>
                            <fieldset class="space-y-3 sm:col-span-3" data-campo-opciones @if(old('tipo') !== 'SELECCION') disabled hidden @endif>
                                <legend class="form-label">Opciones de selección</legend>
                                <div class="space-y-2" data-opciones-lista>
                                    @foreach ((array) (old('opciones') ?: ['']) as $opcion)
                                        <div class="flex gap-2" data-opcion-fila>
                                            <input type="text" name="opciones[]" value="{{ $opcion }}" class="form-control" maxlength="150" placeholder="Nombre de la opción" required>
                                            <button type="button" class="action-button shrink-0 text-red-700" data-quitar-opcion aria-label="Quitar opción">Quitar</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="action-button text-brand" data-agregar-opcion>+ Agregar opción</button>
                            </fieldset><button
                                class="action-button action-button-primary justify-center sm:col-span-3">Guardar
                                campo</button></form>
                </details>@else<div class="py-16 text-center text-sm text-muted">Crea o selecciona una categoría para
                        configurarla.</div>
                @endif
            </section>
        </div>
    </div>
@endsection
