<?php

namespace App\Http\Requests\User;
use App\Http\Requests\User\UserRequest;
use App\Rules\RutValidate;



class StoreUserRequest extends UserRequest
{
    public function rules(): array
    {
        return [
            'rut'      => ['required','string','max:12','unique:users,rut', new RutValidate],
            'name'     => ['required','string','min:6','max:255'],
            'email'    => ['nullable','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
            'roles'    => ['required','array','min:1'],
            'roles.*'  => ['exists:roles,id'],
            'shifts'   => ['nullable','array'],
            'shifts.*' => ['exists:shifts,id'],
        ];
    }
}
