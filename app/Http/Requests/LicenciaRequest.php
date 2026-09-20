<?php

namespace App\Http\Requests;

use App\Models\Licencia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['clave' => Licencia::siguienteClave()]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'clave' => [
                'required',
                'string',
                'max:50',
                Rule::unique('licencias', 'clave'),
            ],
            'nombre' => ['required', 'string', 'max:191'],
            'producto' => ['required', 'string', 'max:191'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'descripcion' => ['nullable', 'string', 'max:5000'],

            'tipo_licencia' => [
                'required',
                'string',
                Rule::in(array_keys(Licencia::TIPOS)),
            ],
            'modalidad' => [
                'nullable',
                'string',
                Rule::in(array_keys(Licencia::MODALIDADES)),
            ],
            'cantidad_adquirida' => ['required', 'integer', 'min:1'],
            'numero_licencia' => ['nullable', 'string', 'max:191'],

            'costo_unitario' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'costo_total' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'moneda' => ['required', 'string', Rule::in(['MXN', 'USD', 'EUR'])],

            'fabricante' => ['nullable', 'string', 'max:150'],
            'version' => ['nullable', 'string', 'max:100'],
            'numero_contrato' => ['nullable', 'string', 'max:100'],
            'clave_producto' => ['nullable', 'string', 'max:5000'],

            'estado' => [
                'required',
                'string',
                Rule::in(array_keys(Licencia::ESTADOS)),
            ],
            'dependencia_id_accesos' => ['required', 'string', 'max:100'],
            'area_id_accesos' => ['nullable', 'string', 'max:100'],
            'responsable_id' => ['nullable', 'integer', 'exists:responsables,id'],

            'fecha_adquisicion' => ['required', 'date'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_adquisicion'],
            'renovacion_automatica' => ['nullable', 'boolean'],

            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser un importe válido.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max' => 'El campo :attribute no debe superar :max.',
            'unique' => 'Ya existe una licencia con esta :attribute.',
            'exists' => 'La opción seleccionada en :attribute no existe.',
            'in' => 'La opción seleccionada en :attribute no es válida.',
            'date' => 'El campo :attribute debe contener una fecha válida.',
            'boolean' => 'El campo :attribute debe indicar sí o no.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de renovación o fin no puede ser anterior a la fecha de contratación.',
        ];
    }

    public function attributes(): array
    {
        return [
            'clave' => 'clave interna',
            'nombre' => 'nombre del registro',
            'producto' => 'software, plataforma o servicio',
            'proveedor_id' => 'proveedor o plataforma',
            'descripcion' => 'uso o propósito',
            'tipo_licencia' => 'tipo de contratación',
            'modalidad' => 'forma de asignación',
            'cantidad_adquirida' => 'número de accesos o licencias',
            'numero_licencia' => 'cuenta, folio o número de licencia',
            'costo_unitario' => 'costo por acceso o licencia',
            'costo_total' => 'costo total de la contratación',
            'moneda' => 'moneda',
            'fabricante' => 'fabricante o editor',
            'version' => 'versión o plan',
            'numero_contrato' => 'número de contrato',
            'clave_producto' => 'código o clave de activación',
            'estado' => 'estado de la licencia',
            'dependencia_id_accesos' => 'dependencia',
            'area_id_accesos' => 'área',
            'responsable_id' => 'persona responsable',
            'fecha_adquisicion' => 'fecha de contratación',
            'fecha_inicio' => 'inicio del acceso',
            'fecha_vencimiento' => 'fecha de renovación o fin',
            'renovacion_automatica' => 'renovación automática',
            'observaciones' => 'notas adicionales',
        ];
    }
}
