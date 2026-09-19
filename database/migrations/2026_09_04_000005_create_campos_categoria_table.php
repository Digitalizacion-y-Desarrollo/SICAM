<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campos_categoria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('clave', 80);
            $table->string('tipo', 30)->default('TEXTO');
            $table->json('opciones')->nullable();
            $table->boolean('requerido')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['categoria_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campos_categoria');
    }
};
