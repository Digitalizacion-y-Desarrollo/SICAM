<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valores_bien', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->cascadeOnDelete();
            $table->foreignId('campo_categoria_id')->constrained('campos_categoria')->cascadeOnDelete();
            $table->text('valor')->nullable();
            $table->timestamps();
            $table->unique(['bien_id', 'campo_categoria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valores_bien');
    }
};
