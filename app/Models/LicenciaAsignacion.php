<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenciaAsignacion extends Model
{
    protected $table = 'licencia_asignaciones';

    protected $fillable = [
        'licencia_id',
        'tipo_asignacion',
        'responsable_id',
        'activo_id',
        'usuario_id_accesos',
        'dependencia_id_accesos',
        'area_id_accesos',
        'fecha_asignacion',
        'fecha_retiro',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'date',
            'fecha_retiro' => 'date',
        ];
    }

    public function licencia(): BelongsTo
    {
        return $this->belongsTo(Licencia::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class)->withTrashed();
    }

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Bien::class, 'activo_id')->withTrashed();
    }
}
