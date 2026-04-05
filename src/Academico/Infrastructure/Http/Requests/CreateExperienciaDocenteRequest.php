<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExperienciaDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institucion' => ['required', 'string', 'max:255'],
            'carrera' => ['required', 'string', 'max:255'],
            'asignaturas' => ['required', 'string'],
            'gestion_periodo' => ['required', 'string'],
            'id_depto' => ['required', 'integer'],
            'archivo_respaldo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
