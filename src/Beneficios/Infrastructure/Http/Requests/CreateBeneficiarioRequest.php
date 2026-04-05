<?php

namespace Src\Beneficios\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBeneficiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'nombres' => ['required', 'string', 'max:150'],
            'ci' => ['nullable', 'string', 'max:30'],
            'complemento' => ['nullable', 'string', 'max:10'],
            'id_ci_expedido' => ['nullable', 'integer'],
            'fecha_nacimiento' => ['required', 'date'],
            'id_parentesco' => ['required', 'integer'],
        ];
    }
}
