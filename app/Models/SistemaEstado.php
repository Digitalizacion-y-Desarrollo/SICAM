<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SistemaEstado extends Model
{
    protected $table = 'sistema_estados';

    protected $fillable = ['sistema_id', 'estado', 'fecha_inicio', 'fecha_fin', 'observaciones'];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'date', 'fecha_fin' => 'date'];
    }

    public function sistema(): BelongsTo
    {
        return $this->belongsTo(Sistema::class);
    }
}
