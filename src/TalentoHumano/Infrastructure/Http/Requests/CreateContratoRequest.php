<?php

namespace Src\TalentoHumano\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_tipo_contrato' => ['required', 'integer', 'exists:tipo_contrato,id_tipo_contrato'],
            'id_area' => ['required', 'integer', 'exists:areas,id_area'],
            'id_cargo' => ['required', 'integer', 'exists:cargos,id_cargo'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'id_sede' => ['required', 'integer', 'exists:sedes,id_sede'],
            'estado_contrato' => ['nullable', 'string', Rule::in(['Activo', 'Borrador', 'Finalizado', 'Vencido'])],
        ];
    }
}
