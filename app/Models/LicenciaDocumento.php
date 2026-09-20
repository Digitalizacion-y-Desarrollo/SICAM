<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenciaDocumento extends Model
{
    protected $table = 'licencia_documentos';

    protected $fillable = [
        'licencia_id',
        'tipo',
        'nombre',
        'archivo',
        'descripcion',
    ];

    public function licencia(): BelongsTo
    {
        return $this->belongsTo(Licencia::class);
    }
}
