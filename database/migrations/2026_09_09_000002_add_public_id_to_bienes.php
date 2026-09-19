<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienes', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->unique();
        });
        DB::table('bienes')->select('id')->orderBy('id')->chunkById(200, function ($bienes) {
            foreach ($bienes as $bien) {
                DB::table('bienes')->where('id', $bien->id)->update(['public_id' => (string) Str::uuid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bienes', fn (Blueprint $table) => $table->dropColumn('public_id'));
    }
};
