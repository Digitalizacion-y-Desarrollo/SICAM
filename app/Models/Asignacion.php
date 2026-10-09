<?php

namespace App\Models;

use App\Models\Concerns\RestringePorAreaUsuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asignacion extends Model
{
    use RestringePorAreaUsuario;
    protected $table = 'asignaciones';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'datetime', 'fecha_fin' => 'datetime'];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class)->withTrashed();
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class)->withTrashed();
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(DepartamentoAcceso::class, 'dependencia_id_accesos')->withTrashed();
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(DepartamentoAcceso::class, 'area_id_accesos')->withTrashed();
    }
}
