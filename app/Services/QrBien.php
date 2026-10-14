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

        return ($formato === 'png' ? new PngWriter : new SvgWriter)->write($qr)->getString();
    }

    /**
     * Genera una etiqueta de impresión sin alterar el contenido del código QR.
     */
    public function etiqueta(Bien $bien, string $formato = 'png'): string
    {
        $bien->loadMissing('dependencia');

        return $formato === 'png' ? $this->etiquetaPng($bien) : $this->etiquetaSvg($bien);
    }

    public function guardar(Bien $bien): void
    {
        if (! Storage::disk('local')->put('qr/'.$bien->public_id.'.svg', $this->imagen($bien))) {
            throw new RuntimeException('No se pudo generar el QR del bien.');
        }
    }

    private function etiquetaPng(Bien $bien): string
    {
        $ancho = 600;
        $alto = 760;
        $etiqueta = imagecreatetruecolor($ancho, $alto);
        $blanco = imagecolorallocate($etiqueta, 255, 255, 255);
        $principal = imagecolorallocate($etiqueta, 96, 22, 51);
        imagefill($etiqueta, 0, 0, $principal);

        $qr = imagecreatefromstring($this->imagen($bien, 'png'));
        imagecopyresampled($etiqueta, $qr, 60, 30, 0, 0, 480, 480, imagesx($qr), imagesy($qr));
        imagedestroy($qr);

        $fuenteRegular = $this->fuente('arial.ttf');
        $fuenteNegrita = $this->fuente('arialbd.ttf');
        $this->centrarTexto($etiqueta, 'FOLIO: '.$bien->folio_sicam, 550, 24, $blanco, $fuenteNegrita);

        $departamento = $bien->dependencia?->nombre ?: (string) $bien->dependencia_id_accesos;
        $lineas = $this->dividirTexto($departamento, 42);
        foreach ($lineas as $indice => $linea) {
            $this->centrarTexto($etiqueta, $linea, 602 + ($indice * 30), 16, $blanco, $fuenteRegular);
        }

        ob_start();
        imagepng($etiqueta);
        $png = ob_get_clean();
        imagedestroy($etiqueta);

        return $png;
    }

    private function etiquetaSvg(Bien $bien): string
    {
        $departamento = $bien->dependencia?->nombre ?: (string) $bien->dependencia_id_accesos;
        $qr = base64_encode($this->imagen($bien));
        $folio = htmlspecialchars('FOLIO: '.$bien->folio_sicam, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $departamento = htmlspecialchars($departamento, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="60mm" height="76mm" viewBox="0 0 600 760">
    <style>@media print { svg { margin: 0; } }</style>
    <rect width="600" height="760" fill="#601633"/>
    <image x="60" y="30" width="480" height="480" href="data:image/svg+xml;base64,{$qr}"/>
    <text x="300" y="550" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#fff">{$folio}</text>
    <text x="300" y="590" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#fff">{$departamento}</text>
</svg>
SVG;
    }

    private function fuente(string $archivo): ?string
    {
        $candidatas = [
            // Fuentes empaquetadas con el proyecto (funcionan en Windows y Linux).
            base_path('resources/fonts/'.$archivo),
            $archivo === 'arialbd.ttf' ? base_path('resources/fonts/DejaVuSans-Bold.ttf') : base_path('resources/fonts/DejaVuSans.ttf'),
            $archivo === 'arialbd.ttf' ? base_path('resources/fonts/LiberationSans-Bold.ttf') : base_path('resources/fonts/LiberationSans-Regular.ttf'),
            // Windows (desarrollo local).
            rtrim((string) getenv('WINDIR'), '\\').DIRECTORY_SEPARATOR.'Fonts'.DIRECTORY_SEPARATOR.$archivo,
            // Linux (producción): DejaVu soporta tildes/ñ y suele estar instalado.
            '/usr/share/fonts/truetype/dejavu/'.($archivo === 'arialbd.ttf' ? 'DejaVuSans-Bold.ttf' : 'DejaVuSans.ttf'),
            '/usr/share/fonts/truetype/liberation/'.($archivo === 'arialbd.ttf' ? 'LiberationSans-Bold.ttf' : 'LiberationSans-Regular.ttf'),
        ];

        foreach ($candidatas as $ruta) {
            if ($ruta && is_file($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    private function centrarTexto($imagen, string $texto, int $y, int $tamano, int $color, ?string $fuente): void
    {
        if ($fuente) {
            $caja = imagettfbbox($tamano, 0, $fuente, $texto);
            imagettftext($imagen, $tamano, 0, (int) ((imagesx($imagen) - ($caja[2] - $caja[0])) / 2), $y, $color, $fuente, $texto);

            return;
        }

        // Sin fuente TTF (servidor sin tipografías): la fuente interna de GD solo
        // maneja ASCII, así que se transliteran tildes/ñ para no imprimir basura.
        $texto = (string) (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto);
        $tamanoInterno = 5;
        imagestring($imagen, $tamanoInterno, (int) ((imagesx($imagen) - imagefontwidth($tamanoInterno) * mb_strlen($texto)) / 2), $y - imagefontheight($tamanoInterno), $texto, $color);
    }

    private function dividirTexto(string $texto, int $limite): array
    {
        return explode("\n", wordwrap($texto, $limite, "\n", true));
    }
}
