<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos_accesos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            // Conserva el identificador que asigna Accesos; no se genera localmente.
            $table->unsignedBigInteger('id')->primary();
            $table->string('nombre', 191);
            $table->string('siglas', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos_accesos');
    }
};
