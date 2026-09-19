<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') DB::statement('ALTER TABLE auditorias ENGINE=InnoDB');
    }

    public function down(): void
    {
        // Conservar el motor transaccional al revertir esta corrección.
    }
};
