<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BuscarProveedorConIa
{
    private const CAMPOS = [
        'razon_social' => 191,
        'rfc' => 20,
        'contacto_nombre' => 191,
        'contacto_email' => 191,
        'contacto_telefono' => 30,
        'sitio_web' => 2048,
        'observaciones' => 5000,
    ];

    public function buscar(string $nombre): array
    {
        $apiKey = config('services.openai.key');
        if (blank($apiKey)) {
            throw new \LogicException('La búsqueda con IA aún no está configurada. Agrega OPENAI_API_KEY al archivo .env.');
        }

        $response = Http::baseUrl('https://api.openai.com/v1')
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(25)
            ->post('/responses', [
                'model' => config('services.openai.provider_lookup_model'),
                'tools' => [['type' => 'web_search', 'search_context_size' => 'low']],
                'input' => $this->instruccion($nombre),
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'datos_proveedor',
                        'strict' => true,
                        'schema' => $this->esquema(),
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI respondió con un error.');
        }

        $respuesta = $response->json();
        $datos = json_decode($this->textoRespuesta($respuesta), true);
        if (! is_array($datos)) {
            throw new RuntimeException('La respuesta de búsqueda no contiene datos válidos.');
        }

        return ['datos' => $this->normalizar($datos), 'fuentes' => $this->fuentes($respuesta)];
    }

    private function instruccion(string $nombre): string
    {
        return <<<PROMPT
Busca en Internet información pública del proveedor o plataforma "{$nombre}".
Devuelve únicamente datos que correspondan inequívocamente a esa entidad y que estén publicados por una fuente confiable, preferentemente su sitio oficial. No inventes ni deduzcas datos. No llenes RFC salvo que esté explícitamente publicado por la entidad o una fuente oficial; si no, usa null. No uses contactos personales. Para cada dato no encontrado o no confiable, usa null. En observaciones incluye una descripción corta y neutral de su servicio, o null.
PROMPT;
    }

    private function esquema(): array
    {
        $campos = [];
        foreach (array_keys(self::CAMPOS) as $campo) {
            $campos[$campo] = ['type' => ['string', 'null']];
        }

        return [
            'type' => 'object',
            'properties' => $campos,
            'required' => array_keys(self::CAMPOS),
            'additionalProperties' => false,
        ];
    }

    private function textoRespuesta(array $respuesta): string
    {
        foreach ($respuesta['output'] ?? [] as $salida) {
            foreach ($salida['content'] ?? [] as $contenido) {
                if (($contenido['type'] ?? null) === 'output_text' && is_string($contenido['text'] ?? null)) {
                    return $contenido['text'];
                }
            }
        }

        return '';
    }

    private function normalizar(array $datos): array
    {
        $normalizados = [];
        foreach (self::CAMPOS as $campo => $longitud) {
            $valor = is_string($datos[$campo] ?? null) ? trim($datos[$campo]) : null;
            $normalizados[$campo] = filled($valor) ? mb_substr($valor, 0, $longitud) : null;
        }

        if ($normalizados['contacto_email'] && ! filter_var($normalizados['contacto_email'], FILTER_VALIDATE_EMAIL)) {
            $normalizados['contacto_email'] = null;
        }
        if ($normalizados['sitio_web'] && ! filter_var($normalizados['sitio_web'], FILTER_VALIDATE_URL)) {
            $normalizados['sitio_web'] = null;
        }

        return $normalizados;
    }

    private function fuentes(array $respuesta): array
    {
        $fuentes = [];
        foreach ($respuesta['output'] ?? [] as $salida) {
            foreach ($salida['content'] ?? [] as $contenido) {
                foreach ($contenido['annotations'] ?? [] as $anotacion) {
                    $url = $anotacion['url'] ?? null;
                    if (($anotacion['type'] ?? null) !== 'url_citation' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                        continue;
                    }
                    $fuentes[$url] = [
                        'url' => $url,
                        'titulo' => mb_substr((string) ($anotacion['title'] ?? parse_url($url, PHP_URL_HOST) ?? $url), 0, 191),
                    ];
                }
            }
        }

        return array_values($fuentes);
    }
}
