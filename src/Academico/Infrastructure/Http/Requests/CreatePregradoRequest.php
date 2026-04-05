<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePregradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nivel' => ['required', 'string', 'max:100'],
            'institucion' => ['required', 'string', 'max:255'],
            'carrera' => ['required', 'string', 'max:255'],
            'id_depto' => ['required', 'integer'],
            'fecha_diploma' => ['nullable', 'date'],
            'fecha_titulo' => ['nullable', 'date'],
            'archivo_diploma' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
            'archivo_titulo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
