<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bien extends Model
{
    use SoftDeletes;

    protected $table = 'bienes';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Bien $bien) {
            $bien->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function valores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ValorBien::class);
    }

    public function movimientos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MovimientoBien::class);
    }

    public function asignaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function asignacionActual(): HasOne
    {
        return $this->hasOne(Asignacion::class)->whereNull('fecha_fin')->latestOfMany();
    }
}
