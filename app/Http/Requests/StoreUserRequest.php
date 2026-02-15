<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RutValido;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rut'      => ['required', 'string', 'max:12', 'unique:users,rut', new RutValido],
            'name'     => ['required', 'string', 'min:6', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', 'unique:users,email'],

            'password' => ['required', 'confirmed', 'min:8'],

            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['exists:roles,id'],

            'shifts'   => ['nullable', 'array'],
            'shifts.*' => ['exists:shifts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'  => 'El campo :attribute es obligatorio.',
            'string'    => 'El campo :attribute debe ser texto.',
            'min'       => 'El campo :attribute debe tener al menos :min caracteres.',
            'max'       => 'El campo :attribute no debe superar los :max caracteres.',
            'email'     => 'El correo electrónico no es válido.',
            'unique'    => 'El :attribute ya está en uso.',
            'confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'rut'      => 'RUT',
            'name'     => 'nombre',
            'email'    => 'correo electrónico',
            'password' => 'contraseña',
            'roles'    => 'roles',
        ];
    }
}
