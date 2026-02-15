<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RutValido;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'rut'      => ['required', 'string', 'max:12', 'unique:users,rut,' . $userId, new RutValido],
            'name'     => ['required', 'string', 'min:6', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', 'unique:users,email,' . $userId],

            'password' => ['nullable', 'confirmed', 'min:8'],

            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['exists:roles,id'],

            'shifts'   => ['nullable', 'array'],
            'shifts.*' => ['exists:shifts,id'],
        ];
    }
}
