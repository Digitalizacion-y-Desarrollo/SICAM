<?php

namespace App\Models;

use App\Models\Concerns\RestringePorDepartamentoDelBien;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoBien extends Model
{
    use RestringePorDepartamentoDelBien;
    protected $table = 'movimientos_bien';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['valor_anterior' => 'array', 'valor_nuevo' => 'array'];
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class)->withTrashed();
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }
}
