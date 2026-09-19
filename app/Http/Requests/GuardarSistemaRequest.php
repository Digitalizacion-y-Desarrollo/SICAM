<?php

namespace App\Http\Requests;

use App\Models\Sistema;
use App\Services\DepartamentosAccesos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarSistemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->clave)) {
            $this->merge(['clave' => mb_strtoupper(trim($this->clave))]);
        }
    }

    public function rules(): array
    {
        return [
            'clave' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/', Rule::unique('sistemas', 'clave')->ignore($this->route('sistema'))],
            'nombre' => ['required', 'string', 'max:191'],
            'descripcion' => ['required', 'string', 'max:10000'],
            'objetivo' => ['nullable', 'string', 'max:10000'],
            'tipo' => ['required', Rule::in(array_keys(Sistema::TIPOS))],
            'origen' => ['required', Rule::in(array_keys(Sistema::ORIGENES))],
            'estado' => ['required', Rule::in(array_keys(Sistema::ESTADOS))],
            'dependencia_id_accesos' => ['required', 'string', 'max:100'],
            'area_id_accesos' => ['nullable', 'string', 'max:100'],
            'responsable_funcional_id' => $this->reglasResponsable('responsable_funcional_id'),
            'responsable_tecnico_id' => $this->reglasResponsable('responsable_tecnico_id'),
            'url_produccion' => ['nullable', 'url:http,https', 'max:2048'],
            'repositorio_url' => ['nullable', 'url:http,https', 'max:2048'],
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_liberacion' => ['nullable', 'date_format:Y-m-d', ...($this->filled('fecha_inicio') ? ['after_or_equal:fecha_inicio'] : [])],
        ];
    }

    private function reglasResponsable(string $campo): array
    {
        $actual = $this->route('sistema')?->getAttribute($campo);

        return ['nullable', 'integer', Rule::exists('responsables', 'id')->where(function ($query) use ($actual) {
            $query->where(fn ($query) => $query->where(fn ($query) => $query->where('activo', true)->whereNull('deleted_at'))->when($actual, fn ($query) => $query->orWhere('id', $actual)));
        })];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach (['url_produccion', 'repositorio_url'] as $campo) {
                if ($this->filled($campo) && (parse_url($this->input($campo), PHP_URL_USER) !== null || parse_url($this->input($campo), PHP_URL_PASS) !== null)) {
                    $validator->errors()->add($campo, 'La URL no debe incluir credenciales.');
                }
            }

            $departamentos = collect(app(DepartamentosAccesos::class)->listar());
            if ($departamentos->isEmpty()) {
                $sistema = $this->route('sistema');
                if (! $sistema || $sistema->dependencia_id_accesos !== $this->input('dependencia_id_accesos') || $sistema->area_id_accesos !== $this->input('area_id_accesos')) {
                    $validator->errors()->add('dependencia_id_accesos', 'No se pudo verificar la organización en Accesos. Intenta de nuevo más tarde.');
                }

                return;
            }

            // La integración actual de SICAM conserva el nombre de Accesos en estos campos.
            $dependencia = $departamentos->whereNull('parent_id')->firstWhere('nombre', $this->input('dependencia_id_accesos'));
            if (! $dependencia) {
                $validator->errors()->add('dependencia_id_accesos', 'Selecciona una dependencia vigente de Accesos.');
            } elseif ($this->filled('area_id_accesos') && ! $departamentos->where('parent_id', $dependencia['id'])->contains('nombre', $this->input('area_id_accesos'))) {
                $validator->errors()->add('area_id_accesos', 'El área debe pertenecer a la dependencia seleccionada.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['descripcion' => 'descripción', 'dependencia_id_accesos' => 'dependencia', 'area_id_accesos' => 'área', 'responsable_funcional_id' => 'responsable funcional', 'responsable_tecnico_id' => 'responsable técnico', 'url_produccion' => 'URL de producción', 'repositorio_url' => 'repositorio', 'fecha_inicio' => 'fecha de inicio', 'fecha_liberacion' => 'fecha de liberación'];
    }
}
