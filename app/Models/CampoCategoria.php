<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampoCategoria extends Model
{
    protected $table = 'campos_categoria';
    protected $fillable = ['categoria_id', 'nombre', 'clave', 'tipo', 'opciones', 'requerido', 'orden', 'activo'];
    protected function casts(): array { return ['opciones' => 'array', 'requerido' => 'boolean', 'activo' => 'boolean']; }
    public function categoria(): BelongsTo { return $this->belongsTo(Categoria::class); }
}
