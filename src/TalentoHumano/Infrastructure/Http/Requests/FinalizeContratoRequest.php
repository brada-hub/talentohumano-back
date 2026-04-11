<?php

namespace Src\TalentoHumano\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeContratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_fin' => ['nullable', 'date'],
        ];
    }
}
