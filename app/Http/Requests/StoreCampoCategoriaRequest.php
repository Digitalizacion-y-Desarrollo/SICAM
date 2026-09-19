<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampoCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        $data = $this->all();
        $opciones = $data['opciones'] ?? null;

        if (is_string($opciones)) {
            $opciones = preg_split('/\r?\n/', $opciones);
        }
        if (is_array($opciones)) {
            $opciones = array_values(array_filter(
                array_map(fn ($opcion) => is_string($opcion) ? trim($opcion) : $opcion, $opciones),
                fn ($opcion) => $opcion !== ''
            ));
        }

        $data['opciones'] = ($data['tipo'] ?? null) === 'SELECCION' ? $opciones : null;

        return $data;
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:120'], 'tipo' => ['required', Rule::in(['TEXTO', 'NUMERO', 'FECHA', 'SELECCION'])],
            'requerido' => ['nullable', 'boolean'], 'activo' => ['sometimes', 'boolean'], 'orden' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'opciones' => ['required_if:tipo,SELECCION', 'nullable', 'array', 'max:100'], 'opciones.*' => ['required', 'string', 'max:150', 'distinct']];
    }
}
