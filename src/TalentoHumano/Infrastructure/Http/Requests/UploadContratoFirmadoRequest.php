<?php

namespace Src\TalentoHumano\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadContratoFirmadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
