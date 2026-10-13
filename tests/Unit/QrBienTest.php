<?php

namespace Tests\Unit;

use App\Models\Bien;
use App\Models\DepartamentoAcceso;
use App\Services\QrBien;
use Tests\TestCase;

class QrBienTest extends TestCase
{
    public function test_svg_label_preserves_utf8_characters_in_the_department_name(): void
    {
        $bien = new Bien([
            'public_id' => '98e8456d-60c7-43ff-be07-17e5740a31eb',
            'folio_sicam' => 'SICAM-2026-000001',
            'dependencia_id_accesos' => 1,
        ]);
        $bien->setRelation('dependencia', new DepartamentoAcceso(['nombre' => 'Administración Pública']));

        $etiqueta = app(QrBien::class)->etiqueta($bien, 'svg');

        $this->assertStringContainsString('Administración Pública', $etiqueta);
    }
}
