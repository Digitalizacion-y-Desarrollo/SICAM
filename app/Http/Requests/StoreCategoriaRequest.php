<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['nombre' => ['required','string','max:120'], 'categoria_padre_id' => ['nullable', 'integer', Rule::exists('categorias','id')->whereNull('deleted_at')], 'descripcion' => ['nullable','string','max:1000'], 'activo' => ['sometimes', 'boolean']]; }
}
