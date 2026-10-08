<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartamentoAcceso extends Model
{
    use SoftDeletes;

    protected $table = 'departamentos_accesos';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'nombre',
        'siglas',
        'descripcion',
        'parent_id',
        'activo',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'parent_id' => 'integer',
            'activo' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
