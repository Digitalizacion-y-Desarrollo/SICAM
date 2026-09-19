<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateBienRequest extends StoreBienRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['numero_serie'] = ['nullable', 'string', 'max:120', Rule::unique('bienes', 'numero_serie')->ignore($this->route('bien'))];

        return $rules;
    }
}
