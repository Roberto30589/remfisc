<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::create([
            'name' => 'Construcción Ruta 5',
            'internal_id' => '01',
            'region' => 'Metropolitana',
            'comuna' => 'Santiago',
            'started_at' => Carbon::now()->subMonths(2),
            'planned_finish_at' => Carbon::now()->addMonths(4),
            'actual_finish_at' => null,
        ]);

        Project::create([
            'name' => 'Mejoramiento Camino Rural',
            'internal_id' => '02',
            'region' => 'Valparaíso',
            'comuna' => 'Quillota',
            'started_at' => Carbon::now()->subMonths(1),
            'planned_finish_at' => Carbon::now()->addMonths(2),
            'actual_finish_at' => null,
        ]);
    }
}
