<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('clave', 50)->unique();
            $table->string('nombre', 191);
            $table->text('descripcion')->nullable();
            $table->string('fabricante', 150)->nullable();
            $table->string('producto', 191);
            $table->string('version', 100)->nullable();
            $table->string('tipo_licencia', 50)->index();
            $table->string('modalidad', 50)->nullable();
            $table->unsignedInteger('cantidad_adquirida')->default(1);
            $table->string('numero_licencia', 191)->nullable();
            $table->text('clave_producto')->nullable();
            $table->string('numero_contrato', 100)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('renovacion_automatica')->default(false);
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->decimal('costo_total', 12, 2)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->string('estado', 30)->default('activa')->index();
            $table->string('dependencia_id_accesos', 100)->nullable()->index();
            $table->string('area_id_accesos', 100)->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->restrictOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencias');
    }
};
