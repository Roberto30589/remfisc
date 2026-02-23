<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceType;
use Illuminate\Support\Str;

class MaintenanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Combustible',
                'unit' => 'litros',
                'requires_quantity' => true,
            ],
            [
                'name' => 'Aceite Motor',
                'unit' => 'litros',
                'requires_quantity' => true,
            ],
            [
                'name' => 'Aceite Hidráulico',
                'unit' => 'litros',
                'requires_quantity' => true,
            ],
            [
                'name' => 'Grasa',
                'unit' => 'kg',
                'requires_quantity' => true,
            ],
            [
                'name' => 'Soldaduras',
                'unit' => 'unidad',
                'requires_quantity' => true,
            ],
            [
                'name' => 'Otros (Detallar)',
                'unit' => null,
                'requires_quantity' => false,
            ],
        ];

        foreach ($types as $type) {
            MaintenanceType::create([
                'name' => $type['name'],
                'slug' => Str::slug($type['name']),
                'unit' => $type['unit'],
                'requires_quantity' => $type['requires_quantity'],
            ]);
        }
    }
}