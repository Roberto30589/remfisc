<?php

namespace App\Http\Requests\Machine;

use App\Http\Requests\BaseRequest;

abstract class MachineRequest extends BaseRequest
{
    public function messages(): array
    {
        return parent::messages();
    }

    public function attributes(): array
    {
        return [
            'internal_id'     => 'ID interno',
            'plate'           => 'patente',
            'machine_type_id' => 'tipo de máquina',
            'brand'           => 'marca',
            'model'           => 'modelo',
            'observations'    => 'observaciones',
            'fuel_type'       => 'tipo de combustible',
            'fuel_capacity'   => 'capacidad de combustible',
        ];
    }
}
