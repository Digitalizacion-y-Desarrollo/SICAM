<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('usuario_id_accesos', 100)->nullable()->index();
            $table->string('usuario_nombre', 191)->nullable();
            $table->string('origen', 30);
            $table->string('accion', 30)->index();
            $table->string('entidad', 100);
            $table->unsignedBigInteger('entidad_id');
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->uuid('solicitud_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
