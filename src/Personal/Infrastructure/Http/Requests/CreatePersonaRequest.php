<?php

namespace Src\Personal\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreatePersonaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'primerApellido'    => ['required', 'string', 'max:100'],
            'segundoApellido'   => ['nullable', 'string', 'max:100'], // Fixed: allow nullable as per DB schema
            'nombres'           => ['required', 'string', 'max:150'],
            'ci'                => ['required', 'regex:/^\d{5,10}$/', 'unique:personas,ci'],
            'complemento'       => ['nullable', 'string', 'max:10'],
            'idCiExpedido'      => ['required', 'string'],
            'idSexo'            => ['required', 'string'],
            'celularPersonal'   => ['required', 'regex:/^[67]\d{7}$/'],
            'correoPersonal'    => ['required', 'email'],
            'estadoCivil'       => ['required', 'string'],
            'idNacionalidad'    => ['required', 'string'],
            'idCiudad'          => ['required', 'string'],
            'idPais'            => ['required', 'string'], // Fixed: Include idPais
            'direccionDomicilio'=> ['nullable', 'string', 'max:255'],
        ];
    }
}
