<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Licencia extends Model
{
    protected $table = 'licencias';

    public const TIPOS = [
        'suscripcion' => 'Suscripción (mensual, anual u otra)',
        'perpetua' => 'Compra única (uso permanente)',
        'volumen' => 'Paquete de varias licencias',
        'oem' => 'Incluida con un equipo (OEM)',
        'gratuita' => 'Acceso o licencia sin costo',
        'open_source' => 'Software de código abierto',
        'prueba' => 'Prueba o cortesía',
        'otra' => 'Otra',
    ];

    public const MODALIDADES = [
        'usuario' => 'Por persona o cuenta',
        'dispositivo' => 'Por equipo o dispositivo',
        'concurrente' => 'Por accesos simultáneos',
        'servidor' => 'Por servidor',
        'sitio' => 'Para una sede o ubicación',
        'corporativa' => 'Para toda la institución',
        'general' => 'Acceso general o compartido',
        'otra' => 'Otra',
    ];

    public const ESTADOS = [
        'activa' => 'Activa',
        'por_vencer' => 'Por vencer',
        'vencida' => 'Vencida',
        'suspendida' => 'Suspendida',
        'cancelada' => 'Cancelada',
    ];

    public const DIAS_POR_VENCER = 30;

    protected $fillable = [
        'clave',
        'nombre',
        'descripcion',
        'fabricante',
        'producto',
        'version',
        'tipo_licencia',
        'modalidad',
        'cantidad_adquirida',
        'numero_licencia',
        'clave_producto',
        'numero_contrato',
        'fecha_adquisicion',
        'fecha_inicio',
        'fecha_vencimiento',
        'renovacion_automatica',
        'costo_unitario',
        'costo_total',
        'moneda',
        'estado',
        'dependencia_id_accesos',
        'area_id_accesos',
        'responsable_id',
        'proveedor_id',
        'observaciones',
    ];

    protected static function booted(): void
    {
        static::saving(function (Licencia $licencia): void {
            if (
                $licencia->tipo_licencia !== 'suscripcion'
                || ! $licencia->fecha_vencimiento
                || in_array($licencia->estado, ['suspendida', 'cancelada'], true)
            ) {
                return;
            }

            $licencia->estado = static::estadoSegunFecha($licencia->fecha_vencimiento);
        });
    }

    public static function estadoSegunFecha(mixed $fechaVencimiento): string
    {
        $fecha = Carbon::parse($fechaVencimiento)->startOfDay();

        if ($fecha->lt(today())) {
            return 'vencida';
        }

        if ($fecha->lte(today()->addDays(static::DIAS_POR_VENCER))) {
            return 'por_vencer';
        }

        return 'activa';
    }

    public static function sincronizarEstadosPorVencimiento(): void
    {
        $estadosManuales = ['suspendida', 'cancelada'];
        $hoy = today()->toDateString();
        $limite = today()->addDays(static::DIAS_POR_VENCER)->toDateString();

        static::query()
            ->where('tipo_licencia', 'suscripcion')
            ->whereNotIn('estado', $estadosManuales)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->update(['estado' => 'vencida']);

        static::query()
            ->where('tipo_licencia', 'suscripcion')
            ->whereNotIn('estado', $estadosManuales)
            ->whereBetween('fecha_vencimiento', [$hoy, $limite])
            ->update(['estado' => 'por_vencer']);

        static::query()
            ->where('tipo_licencia', 'suscripcion')
            ->whereNotIn('estado', $estadosManuales)
            ->whereDate('fecha_vencimiento', '>', $limite)
            ->update(['estado' => 'activa']);
    }

    public static function siguienteClave(): string
    {
        $consecutivo = ((int) static::query()->max('id')) + 1;

        do {
            $clave = 'LIC-'.str_pad((string) $consecutivo, 4, '0', STR_PAD_LEFT);
            $consecutivo++;
        } while (static::query()->where('clave', $clave)->exists());

        return $clave;
    }

    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_vencimiento' => 'date',
            'renovacion_automatica' => 'boolean',
            'cantidad_adquirida' => 'integer',
            'costo_unitario' => 'decimal:2',
            'costo_total' => 'decimal:2',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class)->withTrashed();
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(LicenciaAsignacion::class);
    }

    public function renovaciones(): HasMany
    {
        return $this->hasMany(LicenciaRenovacion::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(LicenciaDocumento::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(LicenciaHistorial::class);
    }

    public function sistemas(): BelongsToMany
    {
        return $this->belongsToMany(Sistema::class, 'licencia_sistema')
            ->withPivot(['cantidad', 'observaciones'])
            ->withTimestamps();
    }
}
