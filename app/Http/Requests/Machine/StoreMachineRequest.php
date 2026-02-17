<?php

namespace App\Http\Requests\Machine;

class StoreMachineRequest extends MachineRequest
{
    public function rules(): array
    {
        return [
            'internal_id' => 'required|unique:machines,internal_id',
            'plate' => 'required|unique:machines,plate',
            'machine_type_id' => 'required|exists:machine_types,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'fuel_type' => 'required|string|max:100',
            'fuel_capacity' => 'required|integer|min:1',
        ];
    }
}
