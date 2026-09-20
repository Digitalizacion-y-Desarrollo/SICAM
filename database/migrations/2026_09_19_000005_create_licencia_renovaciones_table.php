<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencia_renovaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('licencia_id')->constrained('licencias')->cascadeOnDelete();
            $table->date('fecha_renovacion');
            $table->date('vigencia_desde')->nullable();
            $table->date('vigencia_hasta')->nullable();
            $table->decimal('costo', 12, 2)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->string('numero_contrato', 100)->nullable();
            $table->string('numero_factura', 100)->nullable();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencia_renovaciones');
    }
};
