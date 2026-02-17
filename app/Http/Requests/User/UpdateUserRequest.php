<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use App\Rules\RutValidate;
use App\Http\Requests\User\UserRequest;

class UpdateUserRequest extends UserRequest
{
    public function rules(): array
    {
        $userId = $this->route('user');   // ← CAMBIÓ id → user

        return [
            'rut' => [
                'required',
                'string',
                'max:12',
                new RutValidate,
                Rule::unique('users','rut')->ignore($userId),
            ],
            'name' => ['required','string','min:6','max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users','email')->ignore($userId),
            ],
            'password' => ['nullable','confirmed','min:8'],
            'roles'   => ['required','array','min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

}
