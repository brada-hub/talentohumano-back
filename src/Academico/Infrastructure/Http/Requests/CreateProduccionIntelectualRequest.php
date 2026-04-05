<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProduccionIntelectualRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'max:100'],
            'titulo' => ['required', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
            'editorial' => ['nullable', 'string', 'max:255'],
            'id_depto' => ['required', 'integer'],
            'archivo_respaldo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
