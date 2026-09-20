<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('licencia_sistema')) {
            Schema::table('licencia_sistema', function (Blueprint $table) {
                $table->foreign('sistema_id')->references('id')->on('sistemas')->cascadeOnDelete();
                $table->unique(['licencia_id', 'sistema_id']);
            });

            return;
        }

        Schema::create('licencia_sistema', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('licencia_id')->constrained('licencias')->cascadeOnDelete();
            $table->foreignId('sistema_id')->constrained('sistemas')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(1);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique(['licencia_id', 'sistema_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencia_sistema');
    }
};
