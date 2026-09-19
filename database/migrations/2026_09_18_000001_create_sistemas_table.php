<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sistemas', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();
            $table->string('nombre', 191);
            $table->text('descripcion');
            $table->text('objetivo')->nullable();
            $table->string('tipo', 30)->index();
            $table->string('origen', 30);
            $table->string('estado', 30)->index();
            $table->string('dependencia_id_accesos', 100)->index();
            $table->string('area_id_accesos', 100)->nullable();
            $table->foreignId('responsable_funcional_id')->nullable()->constrained('responsables')->restrictOnDelete();
            $table->foreignId('responsable_tecnico_id')->nullable()->constrained('responsables')->restrictOnDelete();
            $table->string('url_produccion', 2048)->nullable();
            $table->string('repositorio_url', 2048)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_liberacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sistemas');
    }
};
