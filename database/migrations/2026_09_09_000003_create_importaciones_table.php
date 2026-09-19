<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('importaciones')) {
        Schema::create('importaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre', 180);
            $table->string('archivo_original', 191);
            $table->string('archivo_path', 191);
            $table->string('estado', 30)->default('PREVIA');
            $table->json('filas');
            $table->json('errores');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('registrados')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        }
        // Reanudar instalaciones interrumpidas por un motor MyISAM predeterminado.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE importaciones ENGINE=InnoDB');
            if (! collect(Schema::getForeignKeys('importaciones'))->contains('name', 'importaciones_categoria_id_foreign')) {
                Schema::table('importaciones', fn (Blueprint $table) => $table->foreign('categoria_id')->references('id')->on('categorias')->restrictOnDelete());
            }
        }
        if (! Schema::hasColumn('bienes', 'importacion_id')) {
        Schema::table('bienes', function (Blueprint $table) {
            $table->foreignId('importacion_id')->nullable()->constrained('importaciones')->restrictOnDelete();
        });
        } elseif (Schema::getConnection()->getDriverName() === 'mysql' && ! collect(Schema::getForeignKeys('bienes'))->contains('name', 'bienes_importacion_id_foreign')) {
            Schema::table('bienes', fn (Blueprint $table) => $table->foreign('importacion_id')->references('id')->on('importaciones')->restrictOnDelete());
        }
    }

    public function down(): void
    {
        Schema::table('bienes', function (Blueprint $table) { $table->dropConstrainedForeignId('importacion_id'); });
        Schema::dropIfExists('importaciones');
    }
};
