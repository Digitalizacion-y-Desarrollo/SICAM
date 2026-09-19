<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responsables', function (Blueprint $table) {
            $table->string('area_id_accesos', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('responsables')->whereNull('area_id_accesos')->update(['area_id_accesos' => '']);

        Schema::table('responsables', function (Blueprint $table) {
            $table->string('area_id_accesos', 100)->nullable(false)->change();
        });
    }
};
