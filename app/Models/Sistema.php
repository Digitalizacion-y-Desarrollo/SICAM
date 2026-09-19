<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sistema extends Model
{
    public const TIPOS = ['web' => 'Sistema web', 'escritorio' => 'Aplicación de escritorio', 'movil' => 'Aplicación móvil', 'api' => 'API', 'servicio' => 'Servicio', 'herramienta' => 'Herramienta interna', 'otro' => 'Otro'];

    public const ORIGENES = ['interno' => 'Desarrollo interno', 'proveedor' => 'Desarrollo por proveedor', 'libre' => 'Software libre', 'comercial' => 'Software comercial', 'otro' => 'Otro'];

    public const ESTADOS = ['desarrollo' => 'En desarrollo', 'pruebas' => 'En pruebas', 'produccion' => 'En producción', 'mantenimiento' => 'En mantenimiento', 'suspendido' => 'Suspendido', 'descontinuado' => 'Descontinuado'];

    protected $fillable = ['clave', 'nombre', 'descripcion', 'objetivo', 'tipo', 'origen', 'estado', 'dependencia_id_accesos', 'area_id_accesos', 'responsable_funcional_id', 'responsable_tecnico_id', 'url_produccion', 'repositorio_url', 'fecha_inicio', 'fecha_liberacion'];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'date', 'fecha_liberacion' => 'date'];
    }

    public function responsableFuncional(): BelongsTo
    {
        return $this->belongsTo(Responsable::class, 'responsable_funcional_id')->withTrashed();
    }

    public function responsableTecnico(): BelongsTo
    {
        return $this->belongsTo(Responsable::class, 'responsable_tecnico_id')->withTrashed();
    }
}
