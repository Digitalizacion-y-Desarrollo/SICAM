<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_procesadas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('sesion_hash', 64);
            $table->string('peticion_hash', 64);
            $table->text('destino')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('solicitudes_procesadas'); }
};
