<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorBien extends Model
{
    protected $table = 'valores_bien';

    protected $guarded = [];

    public function campo(): BelongsTo
    {
        return $this->belongsTo(CampoCategoria::class, 'campo_categoria_id');
    }
}
