<?php

use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\BienPublicoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\ResumenPatrimonioController;
use App\Http\Controllers\SistemaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::prefix('software')->name('software.')->group(function () {

    Route::view('/resumen', 'software.pendiente', [
        'titulo' => 'Resumen'
    ])->name('resumen');

    Route::get('/sistemas', [SistemaController::class, 'index'])
        ->name('sistemas');

    Route::get('/sistemas/nuevo', [SistemaController::class, 'create'])
        ->name('sistemas.create');

    Route::post('/sistemas', [SistemaController::class, 'store'])
        ->name('sistemas.store');

    Route::get('/sistemas/{sistema}/editar', [SistemaController::class, 'edit'])
        ->name('sistemas.edit');

    Route::put('/sistemas/{sistema}', [SistemaController::class, 'update'])
        ->name('sistemas.update');

    Route::get('/sistemas/{sistema}', [SistemaController::class, 'show'])
        ->name('sistemas.show');

    Route::delete(
        '/sistemas/{sistema}',
        [SistemaController::class, 'destroy']
    )->name('sistemas.destroy');



    Route::view('/responsables', 'software.pendiente', [
        'titulo' => 'Responsables'
    ])->name('responsables');

    Route::view('/versiones', 'software.pendiente', [
        'titulo' => 'Versiones'
    ])->name('versiones');

    Route::view('/tecnologias', 'software.pendiente', [
        'titulo' => 'Tecnologías'
    ])->name('tecnologias');

    Route::view('/ambientes', 'software.pendiente', [
        'titulo' => 'Ambientes'
    ])->name('ambientes');

    Route::view('/documentacion', 'software.pendiente', [
        'titulo' => 'Documentación'
    ])->name('documentacion');

    Route::view('/historial', 'software.pendiente', [
        'titulo' => 'Historial de cambios'
    ])->name('historial');
});


Route::get('/patrimonio/bienes', [BienController::class, 'index'])->name('patrimonio.bienes');
Route::get('/patrimonio/bienes/nuevo', [BienController::class, 'create'])->name('patrimonio.bienes.create');
Route::post('/patrimonio/bienes', [BienController::class, 'store'])->name('patrimonio.bienes.store');
Route::get('/patrimonio/bienes/{bien}/editar', [BienController::class, 'edit'])->name('patrimonio.bienes.edit');
Route::get('/patrimonio/bienes/{bien}/qr', [BienController::class, 'qr'])->name('patrimonio.bienes.qr');
Route::get('/patrimonio/bienes/{bien}', [BienController::class, 'show'])->name('patrimonio.bienes.show');
Route::put('/patrimonio/bienes/{bien}', [BienController::class, 'update'])->name('patrimonio.bienes.update');
Route::delete('/patrimonio/bienes/{bien}', [BienController::class, 'destroy'])->name('patrimonio.bienes.destroy');
Route::get('/bien/{publicId}', [BienPublicoController::class, 'show'])->whereUuid('publicId')->name('bienes.publico');
Route::get('/bien/{publicId}/fotografia', [BienPublicoController::class, 'fotografia'])->whereUuid('publicId')->name('bienes.publico.fotografia');
Route::get('/patrimonio/categorias', [CategoriaController::class, 'index'])->name('patrimonio.categorias');
Route::post('/patrimonio/categorias', [CategoriaController::class, 'store'])->name('patrimonio.categorias.store');
Route::post('/patrimonio/categorias/{categoria}/campos', [CategoriaController::class, 'storeCampo'])->name('patrimonio.categorias.campos.store');
Route::delete('/patrimonio/campos/{campo}', [CategoriaController::class, 'destroyCampo'])->name('patrimonio.categorias.campos.destroy');
Route::get('/patrimonio/responsables', [ResponsableController::class, 'index'])->name('patrimonio.responsables');
Route::post('/patrimonio/responsables', [ResponsableController::class, 'store'])->name('patrimonio.responsables.store');
Route::put('/patrimonio/responsables/{responsable}', [ResponsableController::class, 'update'])->name('patrimonio.responsables.update');
Route::delete('/patrimonio/responsables/{responsable}', [ResponsableController::class, 'destroy'])->name('patrimonio.responsables.destroy');
Route::get('/patrimonio/responsables/{responsable}/editar', [ResponsableController::class, 'edit'])->name('patrimonio.responsables.edit');
Route::get('/patrimonio/responsables/{responsable}', [ResponsableController::class, 'show'])->name('patrimonio.responsables.show');
Route::get('/patrimonio/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])->name('patrimonio.categorias.edit');
Route::put('/patrimonio/categorias/{categoria}', [CategoriaController::class, 'update'])->name('patrimonio.categorias.update');
Route::delete('/patrimonio/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('patrimonio.categorias.destroy');
Route::get('/patrimonio/campos/{campo}/editar', [CategoriaController::class, 'editCampo'])->name('patrimonio.campos.edit');
Route::put('/patrimonio/campos/{campo}', [CategoriaController::class, 'updateCampo'])->name('patrimonio.campos.update');
Route::get('/patrimonio/resumen', ResumenPatrimonioController::class)->name('patrimonio.resumen');
Route::resource('/patrimonio/asignaciones', AsignacionController::class)->parameters(['asignaciones' => 'asignacion'])->names('patrimonio.asignaciones');
Route::resource('/patrimonio/movimientos', MovimientoController::class)->parameters(['movimientos' => 'movimiento'])->names('patrimonio.movimientos');
Route::get('/patrimonio/auditoria', [AuditoriaController::class, 'index'])->name('patrimonio.auditoria');
Route::get('/patrimonio/importaciones/plantilla', [ImportacionController::class, 'plantilla'])->name('patrimonio.importaciones.plantilla');
Route::post('/patrimonio/importaciones/{importacion}/confirmar', [ImportacionController::class, 'confirmar'])->name('patrimonio.importaciones.confirmar');
Route::resource('/patrimonio/importaciones', ImportacionController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->parameters(['importaciones' => 'importacion'])->names('patrimonio.importaciones');
