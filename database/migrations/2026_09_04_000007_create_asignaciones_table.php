<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->cascadeOnDelete();
            $table->string('tipo_responsabilidad', 20);
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->restrictOnDelete();
            $table->string('dependencia_id_accesos', 100);
            $table->string('area_id_accesos', 100);
            $table->string('dependencia_nombre', 191)->nullable();
            $table->string('area_nombre', 191)->nullable();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable()->index();
            $table->text('observaciones')->nullable();
            $table->string('creado_por_id_accesos', 100)->nullable()->index();
            $table->timestamps();
            $table->index(['bien_id', 'fecha_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
