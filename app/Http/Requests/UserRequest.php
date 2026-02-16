<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'required'  => 'El campo :attribute es obligatorio.',
            'string'    => 'El campo :attribute debe ser texto.',
            'array'     => 'El campo :attribute debe ser una lista válida.',
            'min'       => 'El campo :attribute debe tener al menos :min caracteres.',
            'max'       => 'El campo :attribute no debe superar los :max caracteres.',
            'email'     => 'El correo electrónico no es válido.',
            'unique'    => 'El :attribute ya está en uso.',
            'exists'    => 'El :attribute seleccionado no es válido.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'rut'        => 'RUT',
            'name'       => 'nombre',
            'email'      => 'correo electrónico',
            'password'   => 'contraseña',
            'roles'      => 'roles',
            'roles.*'    => 'rol',
            'shifts'     => 'turnos',
            'shifts.*'   => 'turno',
        ];
    }
}
