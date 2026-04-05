<?php

namespace Src\Academico\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReconocimientoRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'titulo_premio' => ['required', 'string', 'max:255'],
            'institucion_otorgante' => ['required', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'archivo_respaldo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ];
    }
}
