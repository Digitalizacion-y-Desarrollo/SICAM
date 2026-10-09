<?php

namespace App\Models\Concerns;

use App\Support\AlcanceAreaUsuario;
use Illuminate\Database\Eloquent\Builder;

trait RestringePorAreaUsuario
{
    protected static function bootRestringePorAreaUsuario(): void
    {
        static::addGlobalScope('area-del-usuario', function (Builder $query): void {
            AlcanceAreaUsuario::aplicarDepartamento($query);
        });
    }
}
