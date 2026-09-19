<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bien_id' => ['required', 'integer', Rule::exists('bienes', 'id')->whereNull('deleted_at')],
            'responsabilidad' => ['required', Rule::in(['persona', 'area'])],
            'responsable_id' => ['exclude_unless:responsabilidad,persona', 'required', 'integer', Rule::exists('responsables', 'id')->where('activo', true)->whereNull('deleted_at')],
            'dependencia_id_accesos' => ['required', 'string', 'max:100'], 'area_id_accesos' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', Rule::in(['ASIGNADO', 'EN_RESGUARDO'])], 'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
