<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResponsableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['numero_empleado' => ['nullable', 'string', 'max:50', 'unique:responsables,numero_empleado'], 'nombre' => ['required', 'string', 'max:100'], 'apellido_paterno' => ['nullable', 'string', 'max:100'], 'apellido_materno' => ['nullable', 'string', 'max:100'], 'cargo' => ['nullable', 'string', 'max:150'], 'dependencia_id_accesos' => ['required', 'string', 'max:100'], 'area_id_accesos' => ['nullable', 'string', 'max:100'], 'correo' => ['nullable', 'email', 'max:150'], 'telefono' => ['nullable', 'string', 'max:30']];
    }
}
