<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MachineTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $types = [
        'Excavadora',
        'Cargador Frontal',
        'Retroexcavadora',
        'Camión',
    ];

    foreach ($types as $type) {
        MachineType::create(['name' => $type]);
    }
    }

}
