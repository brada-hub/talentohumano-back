<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCapacitacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'nombre_curso' => ['required', 'string', 'max:255'],
            'institucion' => ['required', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
            'carga_horaria' => ['nullable', 'integer'],
            'id_depto' => ['required', 'integer'],
            'archivo_respaldo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
