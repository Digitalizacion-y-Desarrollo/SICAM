<?php

namespace App\Http\Requests;

use App\Models\CampoCategoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $persona = $this->input('responsabilidad') === 'persona';
        $nuevo = $persona && $this->boolean('crear_responsable');
        $campos = CampoCategoria::where('categoria_id', is_scalar($this->input('categoria_id')) ? $this->input('categoria_id') : null)
            ->where('activo', true)->get();
        $rules = [
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('activo', true)->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:10000'],
            'numero_serie' => ['nullable', 'string', 'max:120', Rule::unique('bienes', 'numero_serie')],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'dependencia_id_accesos' => ['required', 'string', 'max:100'],
            'area_id_accesos' => ['nullable', 'string', 'max:100'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:191'],
            'responsabilidad' => ['required', Rule::in(['persona', 'area', 'ninguna'])],
            'crear_responsable' => ['nullable', 'boolean'],
            'responsable_id' => [Rule::excludeIf(! $persona || $nuevo), 'required', 'integer',
                Rule::exists('responsables', 'id')->where('activo', true)->whereNull('deleted_at')
                    ->where('dependencia_id_accesos', is_string($this->input('dependencia_id_accesos')) ? $this->input('dependencia_id_accesos') : '')
                    ->where(function ($query) {
                        $area = $this->input('area_id_accesos');

                        return is_string($area) && $area !== ''
                            ? $query->where('area_id_accesos', $area)
                            : $query->whereNull('area_id_accesos');
                    })],
            'estado' => ['required', Rule::in($this->input('responsabilidad') === 'ninguna' ? ['DISPONIBLE'] : ['ASIGNADO', 'EN_RESGUARDO'])],
            'fecha_adquisicion' => ['nullable', 'date_format:Y-m-d'],
            'costo' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99', 'decimal:0,2'],
            'proveedor' => ['nullable', 'string', 'max:150'],
            'numero_factura' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:10000'],
            'fotografia' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'campos' => ['nullable', 'array:'.$campos->pluck('id')->implode(',')],
        ];
        foreach (['nombre' => 100, 'apellido_paterno' => 100, 'apellido_materno' => 100, 'numero_empleado' => 50, 'cargo' => 150, 'correo' => 150, 'telefono' => 30] as $key => $max) {
            $rules['nuevo_responsable.'.$key] = [Rule::excludeIf(! $nuevo), $key === 'nombre' ? 'required' : 'nullable', 'string', 'max:'.$max];
        }
        $rules['nuevo_responsable.correo'][] = 'email';
        $rules['nuevo_responsable.numero_empleado'][] = Rule::unique('responsables', 'numero_empleado');
        foreach ($campos as $campo) {
            $rules['campos.'.$campo->id] = [$campo->requerido ? 'required' : 'nullable', ...match ($campo->tipo) {
                'NUMERO' => ['numeric'],
                'FECHA' => ['date_format:Y-m-d'],
                'SELECCION' => ['string', Rule::in($campo->opciones ?? [])],
                default => ['string', 'max:5000'],
            }];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'exists' => 'La selección de :attribute no está disponible o no corresponde a la ubicación indicada.',
            'unique' => 'El valor de :attribute ya está registrado.',
            'in' => 'Selecciona una opción válida para :attribute.',
            'max' => 'El campo :attribute supera el máximo permitido (:max).',
            'image' => 'La fotografía debe ser una imagen.',
            'mimes' => 'La fotografía debe ser JPG o PNG.',
            'fotografia.max' => 'La fotografía no debe superar 5 MB.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'integer' => 'Selecciona un valor válido para :attribute.',
            'string' => 'El campo :attribute debe ser texto.',
            'boolean' => 'Selecciona una opción válida para :attribute.',
            'email' => 'El campo :attribute debe ser un correo válido.',
            'min' => 'El campo :attribute debe ser mayor o igual a :min.',
            'decimal' => 'El campo :attribute debe tener como máximo dos decimales.',
            'date_format' => 'El campo :attribute debe ser una fecha válida.',
            'campos.array' => 'Las características deben pertenecer a la categoría seleccionada.',
        ];
    }

    public function attributes(): array
    {
        $attributes = [
            'categoria_id' => 'categoría', 'nombre' => 'nombre del bien',
            'numero_serie' => 'número de serie', 'dependencia_id_accesos' => 'dependencia',
            'area_id_accesos' => 'área', 'ubicacion_fisica' => 'ubicación física',
            'responsable_id' => 'responsable', 'crear_responsable' => 'nuevo responsable',
            'fecha_adquisicion' => 'fecha de adquisición', 'costo' => 'costo de adquisición',
            'numero_factura' => 'número de factura', 'fotografia' => 'fotografía',
            'nuevo_responsable.nombre' => 'nombre del responsable',
            'nuevo_responsable.apellido_paterno' => 'apellido paterno',
            'nuevo_responsable.apellido_materno' => 'apellido materno',
            'nuevo_responsable.numero_empleado' => 'número de empleado',
            'nuevo_responsable.cargo' => 'cargo', 'nuevo_responsable.correo' => 'correo',
            'nuevo_responsable.telefono' => 'teléfono',
        ];
        foreach (CampoCategoria::where('categoria_id', is_scalar($this->input('categoria_id')) ? $this->input('categoria_id') : null)->where('activo', true)->get() as $campo) {
            $attributes['campos.'.$campo->id] = $campo->nombre;
        }

        return $attributes;
    }
}
