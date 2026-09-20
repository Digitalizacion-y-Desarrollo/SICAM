<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencia_asignaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('licencia_id')->constrained('licencias')->cascadeOnDelete();
            $table->string('tipo_asignacion', 30)->index();
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->nullOnDelete();
            $table->foreignId('activo_id')->nullable()->constrained('bienes')->nullOnDelete();
            $table->string('usuario_id_accesos', 100)->nullable();
            $table->string('dependencia_id_accesos', 100)->nullable();
            $table->string('area_id_accesos', 100)->nullable();
            $table->date('fecha_asignacion')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->string('estado', 30)->default('activa');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencia_asignaciones');
    }
};
