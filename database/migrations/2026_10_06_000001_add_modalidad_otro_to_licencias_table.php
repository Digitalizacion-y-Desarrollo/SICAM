<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licencias', function (Blueprint $table): void {
            $table->string('modalidad_otro', 191)->nullable()->after('modalidad');
        });
    }

    public function down(): void
    {
        Schema::table('licencias', function (Blueprint $table): void {
            $table->dropColumn('modalidad_otro');
        });
    }
};
