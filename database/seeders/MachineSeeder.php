<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Machine;
use App\Models\MachineType;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        $types = MachineType::all();

        if ($types->isEmpty()) {
            $this->command->warn('No hay MachineTypes creados.');
            return;
        }

        Machine::create([
            'internal_id' => '01',
            'plate' => 'ABCD-11',
            'name' => 'Excavadora CAT 320',
            'machine_type_id' => $types->random()->id,
            'fuel_type' => 'Diesel',
            'fuel_capacity' => 300,
        ]);

        Machine::create([
            'internal_id' => '02',
            'plate' => 'EFGH-22',
            'name' => 'Retroexcavadora JCB',
            'machine_type_id' => $types->random()->id,
            'fuel_type' => 'Diesel',
            'fuel_capacity' => 180,
        ]);

        Machine::create([
            'internal_id' => '03',
            'plate' => 'IJKL-33',
            'name' => 'Camión Tolva Volvo',
            'machine_type_id' => $types->random()->id,
            'fuel_type' => 'Diesel',
            'fuel_capacity' => 400,
        ]);
    }
}
