<?php

namespace App\Http\Requests\Machine;

use Illuminate\Validation\Rule;

class UpdateMachineRequest extends MachineRequest
{
    public function rules(): array
    {
        return [
            'internal_id' => [
                'required',
                Rule::unique('machines', 'internal_id')->ignore($this->machine->id),
            ],
            'plate' => [
                'required',
                Rule::unique('machines', 'plate')->ignore($this->machine->id),
            ],
            'machine_type_id' => 'required|exists:machine_types,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'fuel_type' => 'required|string|max:100',
            'fuel_capacity' => 'required|integer|min:1',
        ];
    }
}
