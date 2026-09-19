<?php

namespace App\Services;

use App\Models\Sistema;
use Illuminate\Support\Facades\DB;

class GuardarSistema
{
    public function ejecutar(array $datos, ?Sistema $sistema = null): Sistema
    {
        return DB::transaction(function () use ($datos, $sistema) {
            $sistema = $sistema ? Sistema::query()->lockForUpdate()->findOrFail($sistema->id) : new Sistema;
            $sistema->fill($datos)->save();

            return $sistema;
        });
    }
}
