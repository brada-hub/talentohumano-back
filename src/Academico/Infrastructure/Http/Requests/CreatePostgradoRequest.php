<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostgradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'max:100'],
            'nombre_programa' => ['required', 'string', 'max:255'],
            'institucion' => ['required', 'string', 'max:255'],
            'id_depto' => ['required', 'integer'],
            'fecha_diploma' => ['nullable', 'date'],
            'fecha_certificacion' => ['nullable', 'date'],
            'archivo_respaldo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
