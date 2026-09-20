<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenciaRenovacion extends Model
{
    protected $table = 'licencia_renovaciones';

    protected $fillable = [
        'licencia_id',
        'fecha_renovacion',
        'vigencia_desde',
        'vigencia_hasta',
        'costo',
        'moneda',
        'numero_contrato',
        'numero_factura',
        'proveedor_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_renovacion' => 'date',
            'vigencia_desde' => 'date',
            'vigencia_hasta' => 'date',
            'costo' => 'decimal:2',
        ];
    }

    public function licencia(): BelongsTo
    {
        return $this->belongsTo(Licencia::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}
