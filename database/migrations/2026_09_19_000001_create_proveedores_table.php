<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('nombre', 191);
            $table->string('razon_social', 191)->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('contacto_nombre', 191)->nullable();
            $table->string('contacto_email', 191)->nullable();
            $table->string('contacto_telefono', 30)->nullable();
            $table->string('sitio_web', 2048)->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
