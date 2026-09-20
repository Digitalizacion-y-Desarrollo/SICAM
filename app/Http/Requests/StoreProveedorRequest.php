<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->has('activo') ? $this->boolean('activo') : true]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:191'],
            'razon_social' => ['nullable', 'string', 'max:191'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'contacto_nombre' => ['nullable', 'string', 'max:191'],
            'contacto_email' => ['nullable', 'email', 'max:191'],
            'contacto_telefono' => ['nullable', 'string', 'max:30'],
            'sitio_web' => ['nullable', 'url:http,https', 'max:2048'],
            'activo' => ['required', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'email' => 'El campo :attribute debe contener un correo válido.',
            'url' => 'El campo :attribute debe contener una dirección web válida.',
            'boolean' => 'El campo :attribute debe indicar sí o no.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre del proveedor o plataforma',
            'razon_social' => 'razón social',
            'rfc' => 'RFC',
            'contacto_nombre' => 'persona de contacto',
            'contacto_email' => 'correo de contacto',
            'contacto_telefono' => 'teléfono de contacto',
            'sitio_web' => 'sitio web',
            'activo' => 'estado',
            'observaciones' => 'observaciones',
        ];
    }
}
