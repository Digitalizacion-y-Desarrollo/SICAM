<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Importacion extends Model
{
    use SoftDeletes;

    protected $table = 'importaciones';

    protected $guarded = [];

    protected function casts(): array { return ['filas' => 'array', 'errores' => 'array']; }

    public function categoria(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(Categoria::class)->withTrashed(); }

    public function bienes(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(Bien::class); }
}
