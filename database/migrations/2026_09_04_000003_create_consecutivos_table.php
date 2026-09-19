<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consecutivos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('tipo', 40);
            $table->unsignedSmallInteger('anio')->default(0);
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->timestamps();
            $table->unique(['tipo', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consecutivos');
    }
};
