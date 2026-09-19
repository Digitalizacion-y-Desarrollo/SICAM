<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsable extends Model
{
    use SoftDeletes;
    protected $fillable = ['numero_empleado', 'nombre', 'apellido_paterno', 'apellido_materno', 'cargo', 'dependencia_id_accesos', 'area_id_accesos', 'correo', 'telefono', 'activo'];
    protected $appends = ['nombre_completo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
    protected function nombreCompleto(): Attribute { return Attribute::get(fn () => trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}")); }
    public function asignaciones(): HasMany { return $this->hasMany(Asignacion::class); }
}
