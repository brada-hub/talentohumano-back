<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateIdiomaRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'idioma' => ['required', 'string', 'max:100'],
            'nivel_habla' => ['required', 'string', 'max:100'],
            'nivel_escritura' => ['required', 'string', 'max:100'],
            'nivel_lee' => ['required', 'string', 'max:100'],
            'archivo_respaldo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
