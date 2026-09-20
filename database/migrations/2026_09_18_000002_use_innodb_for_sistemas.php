<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE sistemas ENGINE=InnoDB');
        }
    }

    public function down(): void
    {
        // Se conserva InnoDB porque las relaciones de licencias dependen de este motor.
    }
};
