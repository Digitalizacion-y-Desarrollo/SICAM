<?php

namespace Tests\Feature;

use App\Models\Licencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenciaVencimientoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_estado_se_calcula_segun_la_fecha_de_vencimiento(): void
    {
        $vencida = $this->crearLicencia('LIC-VENCIDA', today()->subDay(), 'activa');
        $venceHoy = $this->crearLicencia('LIC-HOY', today(), 'activa');
        $porVencer = $this->crearLicencia('LIC-30-DIAS', today()->addDays(30), 'activa');
        $activa = $this->crearLicencia('LIC-ACTIVA', today()->addDays(31), 'vencida');

        $this->assertSame('vencida', $vencida->estado);
        $this->assertSame('por_vencer', $venceHoy->estado);
        $this->assertSame('por_vencer', $porVencer->estado);
        $this->assertSame('activa', $activa->estado);
    }

    public function test_estado_no_cambia_automaticamente_si_no_es_suscripcion(): void
    {
        $perpetua = $this->crearLicencia('LIC-PERPETUA', today()->subDay(), 'activa', 'perpetua');
        $oem = $this->crearLicencia('LIC-OEM', today()->addDays(5), 'activa', 'oem');

        Licencia::sincronizarEstadosPorVencimiento();

        $this->assertSame('activa', $perpetua->fresh()->estado);
        $this->assertSame('activa', $oem->fresh()->estado);
    }

    public function test_sincronizacion_actualiza_estados_al_avanzar_el_tiempo(): void
    {
        $licencia = $this->crearLicencia('LIC-CAMBIO', today()->addDays(31), 'activa');

        $this->travel(2)->days();
        Licencia::sincronizarEstadosPorVencimiento();

        $this->assertSame('por_vencer', $licencia->fresh()->estado);
    }

    public function test_renovacion_automatica_se_desactiva_al_desmarcarla_en_edicion(): void
    {
        $licencia = $this->crearLicencia('LIC-RENOVACION', today()->addYear(), 'activa');
        $licencia->update(['renovacion_automatica' => true]);

        $this->put(route('licencia.update', $licencia), [
            'nombre' => $licencia->nombre,
            'producto' => $licencia->producto,
            'tipo_licencia' => $licencia->tipo_licencia,
            'cantidad_adquirida' => 1,
            'costo_unitario' => 0,
            'costo_total' => 0,
            'moneda' => 'MXN',
            'estado' => 'activa',
            'dependencia_id_accesos' => 'Tecnologías de la Información',
            'fecha_adquisicion' => today()->toDateString(),
            'fecha_vencimiento' => today()->addYear()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertFalse($licencia->fresh()->renovacion_automatica);
    }

    public function test_estados_manuales_no_se_sobrescriben(): void
    {
        $suspendida = $this->crearLicencia('LIC-SUSPENDIDA', today()->subDay(), 'suspendida');
        $cancelada = $this->crearLicencia('LIC-CANCELADA', today()->addDays(5), 'cancelada');

        Licencia::sincronizarEstadosPorVencimiento();

        $this->assertSame('suspendida', $suspendida->fresh()->estado);
        $this->assertSame('cancelada', $cancelada->fresh()->estado);
    }

    private function crearLicencia(
        string $clave,
        mixed $fechaVencimiento,
        string $estado,
        string $tipoLicencia = 'suscripcion',
    ): Licencia {
        return Licencia::create([
            'clave' => $clave,
            'nombre' => $clave,
            'producto' => 'Producto de prueba',
            'tipo_licencia' => $tipoLicencia,
            'cantidad_adquirida' => 1,
            'estado' => $estado,
            'fecha_vencimiento' => $fechaVencimiento,
        ]);
    }
}
