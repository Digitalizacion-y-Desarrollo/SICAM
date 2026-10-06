<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sistemas')
            ->orderBy('id')
            ->eachById(function (object $sistema): void {
                $existe = DB::table('sistema_estados')
                    ->where('sistema_id', $sistema->id)
                    ->exists();

                if ($existe) {
                    return;
                }

                $fecha = $sistema->fecha_inicio ?: $sistema->created_at ?: now();

                DB::table('sistema_estados')->insert([
                    'sistema_id' => $sistema->id,
                    'estado' => $sistema->estado,
                    'fecha_inicio' => $fecha,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Los registros iniciales se conservan para no eliminar historial válido.
    }
};
