<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('folio_sicam', 32)->unique();
            $table->string('numero_patrimonial', 50)->unique();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre', 180);
            $table->text('descripcion')->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 120)->nullable()->unique();
            $table->string('dependencia_id_accesos', 100)->index();
            $table->string('area_id_accesos', 100)->index();
            $table->string('ubicacion_fisica', 191)->nullable();
            $table->string('estado', 40)->default('DISPONIBLE')->index();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('costo', 14, 2)->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->string('numero_factura', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('fotografia_path', 191)->nullable();
            $table->string('creado_por_id_accesos', 100)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienes');
    }
};
