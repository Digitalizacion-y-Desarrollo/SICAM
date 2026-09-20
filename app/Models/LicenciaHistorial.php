<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenciaHistorial extends Model
{
    protected $table = 'licencia_historial';

    protected $fillable = [
        'licencia_id',
        'accion',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'usuario_id_accesos',
        'usuario_nombre',
        'descripcion',
    ];

    public function licencia(): BelongsTo
    {
        return $this->belongsTo(Licencia::class);
    }
}
