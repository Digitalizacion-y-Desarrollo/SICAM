<?php

namespace App\Http\Requests;

use App\Models\Sistema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SistemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sistema = $this->route('sistema');

        $sistemaId = $sistema instanceof Sistema
            ? $sistema->id
            : $sistema;

        return [

            'clave' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sistemas', 'clave')
                    ->ignore($sistemaId),
            ],

            'nombre' => [
                'required',
                'string',
                'max:180',
            ],

            'descripcion' => [
                'required',
                'string',
                'max:5000',
            ],

            'objetivo' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'tipo' => [
                'required',
                Rule::in(array_keys(Sistema::TIPOS)),
            ],

            'origen' => [
                'required',
                Rule::in(array_keys(Sistema::ORIGENES)),
            ],

            'estado' => [
                'required',
                Rule::in(array_keys(Sistema::ESTADOS)),
            ],

            'dependencia_id_accesos' => [
                'required',
                'string',
                'max:100',
            ],

            'area_id_accesos' => [
                'nullable',
                'string',
                'max:100',
            ],

            'responsable_funcional_id' => [
                'nullable',
                'integer',
                'exists:responsables,id',
            ],

            'responsable_tecnico_id' => [
                'nullable',
                'integer',
                'exists:responsables,id',
            ],

            'url_produccion' => [
                'nullable',
                'url',
                'max:500',
            ],

            'repositorio_url' => [
                'nullable',
                'url',
                'max:500',
            ],

            'fecha_inicio' => [
                'nullable',
                'date',
            ],

            'fecha_liberacion' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'clave.required' =>
                'La clave del sistema es obligatoria.',

            'clave.max' =>
                'La clave no puede superar los 50 caracteres.',

            'clave.unique' =>
                'Ya existe un sistema registrado con esta clave.',

            'nombre.required' =>
                'El nombre del aplicativo es obligatorio.',

            'nombre.max' =>
                'El nombre no puede superar los 180 caracteres.',

            'descripcion.required' =>
                'La descripción es obligatoria.',

            'descripcion.max' =>
                'La descripción no puede superar los 5,000 caracteres.',

            'objetivo.max' =>
                'El objetivo no puede superar los 5,000 caracteres.',

            'tipo.required' =>
                'Debes seleccionar un tipo de sistema.',

            'tipo.in' =>
                'El tipo de sistema seleccionado no es válido.',

            'origen.required' =>
                'Debes seleccionar el origen del sistema.',

            'origen.in' =>
                'El origen seleccionado no es válido.',

            'estado.required' =>
                'Debes seleccionar el estado del sistema.',

            'estado.in' =>
                'El estado seleccionado no es válido.',

            'dependencia_id_accesos.required' =>
                'Debes seleccionar una dependencia.',

            'responsable_funcional_id.exists' =>
                'El responsable funcional seleccionado no existe.',

            'responsable_tecnico_id.exists' =>
                'El responsable técnico seleccionado no existe.',

            'url_produccion.url' =>
                'La URL de producción debe ser válida.',

            'repositorio_url.url' =>
                'La URL del repositorio debe ser válida.',

            'fecha_inicio.date' =>
                'La fecha de inicio no es válida.',

            'fecha_liberacion.date' =>
                'La fecha de liberación no es válida.',

            'fecha_liberacion.after_or_equal' =>
                'La fecha de liberación no puede ser anterior a la fecha de inicio.',
        ];
    }
}
