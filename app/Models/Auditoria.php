<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['valores_anteriores' => 'array', 'valores_nuevos' => 'array', 'created_at' => 'datetime'];
    }
}
