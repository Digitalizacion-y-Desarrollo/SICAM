<?php

use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\BienPublicoController;
use App\Http\Controllers\BusquedaGlobalController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\LicenciaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ResumenPatrimonioController;
use App\Http\Controllers\SistemaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/bien/{publicId}', [BienPublicoController::class, 'show'])->whereUuid('publicId')->name('bienes.publico');
Route::get('/bien/{publicId}/fotografia', [BienPublicoController::class, 'fotografia'])->whereUuid('publicId')->name('bienes.publico.fotografia');

Route::middleware('auth')->group(function () {
    Route::view('/sin-permisos', 'errors.sin-permisos')->name('sin-permisos');
    Route::get('/buscar', BusquedaGlobalController::class)->middleware(['can:busqueda.global', 'throttle:60,1'])->name('busqueda.global');
    Route::get('/', DashboardController::class)->middleware('can:dashboard-sicam.ver')->name('dashboard');

    Route::prefix('licencia')->name('licencia.')->group(function () {
        Route::get('/resumen', [LicenciaController::class, 'resumen'])->middleware('can:licencias.ver')->name('resumen');
        Route::get('/proveedores', [ProveedorController::class, 'index'])->middleware('can:proveedores.ver')->name('proveedores');
        Route::post('/proveedores', [ProveedorController::class, 'store'])->middleware('can:proveedores.crear')->name('proveedores.store');
        Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])->middleware('can:proveedores.editar')->name('proveedores.edit');
        Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->middleware('can:proveedores.editar')->name('proveedores.update');
        Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->middleware('can:proveedores.eliminar')->name('proveedores.destroy');
        Route::get('/', [LicenciaController::class, 'index'])->middleware('can:licencias.ver')->name('index');
        Route::get('/create', [LicenciaController::class, 'create'])->middleware('can:licencias.crear')->name('create');
        Route::post('/', [LicenciaController::class, 'store'])->middleware('can:licencias.crear')->name('store');
        Route::get('/{licencia}/editar', [LicenciaController::class, 'edit'])->middleware('can:licencias.editar')->name('editar');
        Route::put('/{licencia}/update', [LicenciaController::class, 'update'])->middleware('can:licencias.editar')->name('update');
        Route::delete('/{licencia}/destroy', [LicenciaController::class, 'destroy'])->middleware('can:licencias.eliminar')->name('destroy');
    });

    Route::prefix('software')->name('software.')->group(function () {
        Route::get('/resumen', [SistemaController::class, 'resumen'])->middleware('can:software.ver')->name('resumen');
        Route::get('/sistemas', [SistemaController::class, 'index'])->middleware('can:sistemas.ver')->name('sistemas');
        Route::get('/sistemas/nuevo', [SistemaController::class, 'create'])->middleware('can:sistemas.crear')->name('sistemas.create');
        Route::post('/sistemas', [SistemaController::class, 'store'])->middleware('can:sistemas.crear')->name('sistemas.store');
        Route::get('/sistemas/{sistema}/editar', [SistemaController::class, 'edit'])->middleware('can:sistemas.editar')->name('sistemas.edit');
        Route::put('/sistemas/{sistema}', [SistemaController::class, 'update'])->middleware('can:sistemas.editar')->name('sistemas.update');
        Route::get('/sistemas/{sistema}', [SistemaController::class, 'show'])->middleware('can:sistemas.ver')->name('sistemas.show');
        Route::delete('/sistemas/{sistema}', [SistemaController::class, 'destroy'])->middleware('can:sistemas.eliminar')->name('sistemas.destroy');
    });

    Route::prefix('patrimonio')->name('patrimonio.')->group(function () {
        Route::get('/resumen', ResumenPatrimonioController::class)->middleware('can:patrimonio.ver')->name('resumen');

        Route::get('/bienes', [BienController::class, 'index'])->middleware('can:bienes.ver')->name('bienes');
        Route::get('/bienes/nuevo', [BienController::class, 'create'])->middleware('can:bienes.crear')->name('bienes.create');
        Route::post('/bienes', [BienController::class, 'store'])->middleware('can:bienes.crear')->name('bienes.store');
        Route::get('/bienes/{bien}/editar', [BienController::class, 'edit'])->middleware('can:bienes.editar')->name('bienes.edit');
        Route::put('/bienes/{bien}', [BienController::class, 'update'])->middleware('can:bienes.editar')->name('bienes.update');
        Route::delete('/bienes/{bien}', [BienController::class, 'destroy'])->middleware('can:bienes.eliminar')->name('bienes.destroy');
        Route::get('/bienes/{bien}/qr', [BienController::class, 'qr'])->middleware('can:bienes.ver')->name('bienes.qr');
        Route::get('/bienes/{bien}', [BienController::class, 'show'])->middleware('can:bienes.ver')->name('bienes.show');

        Route::get('/categorias', [CategoriaController::class, 'index'])->middleware('can:categorias.ver')->name('categorias');
        Route::post('/categorias', [CategoriaController::class, 'store'])->middleware('can:categorias.crear')->name('categorias.store');
        Route::post('/categorias/{categoria}/campos', [CategoriaController::class, 'storeCampo'])->middleware('can:categorias.crear')->name('categorias.campos.store');
        Route::get('/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])->middleware('can:categorias.editar')->name('categorias.edit');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->middleware('can:categorias.editar')->name('categorias.update');
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->middleware('can:categorias.eliminar')->name('categorias.destroy');
        Route::get('/campos/{campo}/editar', [CategoriaController::class, 'editCampo'])->middleware('can:categorias.editar')->name('campos.edit');
        Route::put('/campos/{campo}', [CategoriaController::class, 'updateCampo'])->middleware('can:categorias.editar')->name('campos.update');
        Route::delete('/campos/{campo}', [CategoriaController::class, 'destroyCampo'])->middleware('can:categorias.eliminar')->name('categorias.campos.destroy');

        Route::get('/responsables', [ResponsableController::class, 'index'])->middleware('can:responsables.ver')->name('responsables');
        Route::post('/responsables', [ResponsableController::class, 'store'])->middleware('can:responsables.crear')->name('responsables.store');
        Route::get('/responsables/{responsable}/editar', [ResponsableController::class, 'edit'])->middleware('can:responsables.editar')->name('responsables.edit');
        Route::put('/responsables/{responsable}', [ResponsableController::class, 'update'])->middleware('can:responsables.editar')->name('responsables.update');
        Route::delete('/responsables/{responsable}', [ResponsableController::class, 'destroy'])->middleware('can:responsables.eliminar')->name('responsables.destroy');
        Route::get('/responsables/{responsable}', [ResponsableController::class, 'show'])->middleware('can:responsables.ver')->name('responsables.show');

        Route::resource('asignaciones', AsignacionController::class)->only(['index', 'show'])->middleware('can:asignaciones.ver')->parameters(['asignaciones' => 'asignacion'])->names('asignaciones');
        Route::resource('asignaciones', AsignacionController::class)->only(['create', 'store'])->middleware('can:asignaciones.crear')->parameters(['asignaciones' => 'asignacion'])->names('asignaciones');
        Route::resource('asignaciones', AsignacionController::class)->only(['edit', 'update'])->middleware('can:asignaciones.editar')->parameters(['asignaciones' => 'asignacion'])->names('asignaciones');
        Route::resource('asignaciones', AsignacionController::class)->only('destroy')->middleware('can:asignaciones.eliminar')->parameters(['asignaciones' => 'asignacion'])->names('asignaciones');

        Route::resource('movimientos', MovimientoController::class)->only(['index', 'show'])->middleware('can:movimientos.ver')->parameters(['movimientos' => 'movimiento'])->names('movimientos');
        Route::resource('movimientos', MovimientoController::class)->only(['create', 'store'])->middleware('can:movimientos.crear')->parameters(['movimientos' => 'movimiento'])->names('movimientos');
        Route::resource('movimientos', MovimientoController::class)->only(['edit', 'update'])->middleware('can:movimientos.editar')->parameters(['movimientos' => 'movimiento'])->names('movimientos');
        Route::resource('movimientos', MovimientoController::class)->only('destroy')->middleware('can:movimientos.eliminar')->parameters(['movimientos' => 'movimiento'])->names('movimientos');

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->middleware('can:auditoria.ver')->name('auditoria');
        Route::get('/importaciones/plantilla', [ImportacionController::class, 'plantilla'])->middleware('can:importaciones.ver')->name('importaciones.plantilla');
        Route::post('/importaciones/{importacion}/confirmar', [ImportacionController::class, 'confirmar'])->middleware('can:importaciones.editar')->name('importaciones.confirmar');
        Route::resource('importaciones', ImportacionController::class)->only(['index', 'show'])->middleware('can:importaciones.ver')->parameters(['importaciones' => 'importacion'])->names('importaciones');
        Route::resource('importaciones', ImportacionController::class)->only('store')->middleware('can:importaciones.crear')->parameters(['importaciones' => 'importacion'])->names('importaciones');
        Route::resource('importaciones', ImportacionController::class)->only('update')->middleware('can:importaciones.editar')->parameters(['importaciones' => 'importacion'])->names('importaciones');
        Route::resource('importaciones', ImportacionController::class)->only('destroy')->middleware('can:importaciones.eliminar')->parameters(['importaciones' => 'importacion'])->names('importaciones');
    });
});
