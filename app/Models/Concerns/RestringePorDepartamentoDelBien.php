<?php

namespace App\Models\Concerns;

use App\Support\AlcanceAreaUsuario;
use Illuminate\Database\Eloquent\Builder;

trait RestringePorDepartamentoDelBien
{
    protected static function bootRestringePorDepartamentoDelBien(): void
    {
        static::addGlobalScope('departamento-del-bien', function (Builder $query): void {
            AlcanceAreaUsuario::aplicarPorBien($query);
        });
    }
}
