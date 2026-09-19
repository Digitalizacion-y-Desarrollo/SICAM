<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('numero_empleado', 50)->nullable()->unique();
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100)->nullable();
            $table->string('apellido_materno', 100)->nullable();
            $table->string('cargo', 150)->nullable();
            $table->string('dependencia_id_accesos', 100)->index();
            $table->string('area_id_accesos', 100)->index();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
