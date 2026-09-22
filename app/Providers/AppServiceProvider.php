<?php

namespace App\Providers;

use App\Models\Asignacion;
use App\Models\Bien;
use App\Models\CampoCategoria;
use App\Models\Categoria;
use App\Models\Importacion;
use App\Models\MovimientoBien;
use App\Models\Responsable;
use App\Models\Sistema;
use App\Models\ValorBien;
use App\Observers\AuditoriaObserver;
use App\Support\PermisosAccesos;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        foreach (PermisosAccesos::permisos() as $permiso) {
            Gate::define($permiso, fn () => PermisosAccesos::permite($permiso));
        }

        Gate::define('busqueda.global', fn () => collect([
            'bienes.ver',
            'categorias.ver',
            'responsables.ver',
            'sistemas.ver',
            'licencias.ver',
            'proveedores.ver',
        ])->contains(fn (string $permiso) => PermisosAccesos::permite($permiso)));

        Sistema::observe(AuditoriaObserver::class);
        foreach ([Bien::class, Categoria::class, CampoCategoria::class, Responsable::class, Asignacion::class, ValorBien::class, MovimientoBien::class, Importacion::class] as $model) {
            $model::observe(AuditoriaObserver::class);
        }
    }
}
