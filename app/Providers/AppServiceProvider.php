<?php

namespace App\Providers;

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
        \App\Models\Sistema::observe(\App\Observers\AuditoriaObserver::class);
        foreach ([\App\Models\Bien::class, \App\Models\Categoria::class, \App\Models\CampoCategoria::class, \App\Models\Responsable::class, \App\Models\Asignacion::class, \App\Models\ValorBien::class, \App\Models\MovimientoBien::class, \App\Models\Importacion::class] as $model) {
            $model::observe(\App\Observers\AuditoriaObserver::class);
        }
    }
}
