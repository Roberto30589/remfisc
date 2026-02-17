<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

abstract class UserRequest extends BaseRequest
{
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'unique' => 'El :attribute ya está en uso.',
        ]);
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
