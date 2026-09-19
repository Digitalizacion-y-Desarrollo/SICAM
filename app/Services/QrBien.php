<?php

namespace App\Services;

use App\Models\Bien;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class QrBien
{
    public function url(Bien $bien): string
    {
        return rtrim(config('app.url'), '/').'/bien/'.$bien->public_id;
    }

    public function imagen(Bien $bien, string $formato = 'svg'): string
    {
        $qr = new QrCode(data: $this->url($bien), errorCorrectionLevel: ErrorCorrectionLevel::Medium, size: 600, margin: 40);

        return ($formato === 'png' ? new PngWriter() : new SvgWriter())->write($qr)->getString();
    }

    public function guardar(Bien $bien): void
    {
        if (! Storage::disk('local')->put('qr/'.$bien->public_id.'.svg', $this->imagen($bien))) {
            throw new RuntimeException('No se pudo generar el QR del bien.');
        }
    }
}
