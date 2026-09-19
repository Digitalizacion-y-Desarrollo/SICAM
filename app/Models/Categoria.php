<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    protected $fillable = ['categoria_padre_id', 'nombre', 'slug', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function padre(): BelongsTo { return $this->belongsTo(self::class, 'categoria_padre_id'); }
    public function hijas(): HasMany { return $this->hasMany(self::class, 'categoria_padre_id')->orderBy('nombre'); }
    public function campos(): HasMany { return $this->hasMany(CampoCategoria::class)->orderBy('orden'); }
    public function bienes(): HasMany { return $this->hasMany(Bien::class); }
}
