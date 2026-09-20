<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'razon_social',
        'rfc',
        'contacto_nombre',
        'contacto_email',
        'contacto_telefono',
        'sitio_web',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function licencias(): HasMany
    {
        return $this->hasMany(Licencia::class);
    }

    public function renovaciones(): HasMany
    {
        return $this->hasMany(LicenciaRenovacion::class);
    }
}
