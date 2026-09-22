<?php

use App\Models\Licencia;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => Licencia::sincronizarEstadosPorVencimiento())
    ->dailyAt('00:05')
    ->name('licencias:sincronizar-estados')
    ->withoutOverlapping();
