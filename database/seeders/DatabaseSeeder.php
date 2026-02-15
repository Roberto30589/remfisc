<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $superRole  = Role::where('name', 'Super-Administrador')->first();
        $adminRole  = Role::where('name', 'Administrador')->first();
        $driverRole = Role::where('name', 'Conductor')->first();

        $adminUser = User::firstOrCreate(
            ['rut' => '19.874.109-4'], // RUT único
            [
                'name'     => 'Matias Vera Carcamo',
                'email'    => 'matias18698@gmail.com',
                'password' => Hash::make('123456789'),
            ]
        );

        if (! $adminUser->hasRole('Super-Administrador')) {
            $adminUser->assignRole($superRole);
        }

        $adminUser = User::firstOrCreate(
            ['rut' => '16.978.624-0'], // RUT único
            [
                'name'     => 'Roberto Sierra Vera',
                'email'    => 'roberto30589@gmail.com',
                'password' => Hash::make('123456789'),
            ]
        );

        if (! $adminUser->hasRole('Super-Administrador')) {
            $adminUser->assignRole($superRole);
        }


        $driverUser = User::firstOrCreate(
        ['rut' => '11.111.111-9'], // RUT válido
        [
            'name'     => 'Conductor Prueba',
            'email'    => 'conductor@gmail.com',
            'password' => Hash::make('123456789'),
        ]
      );

        if (! $driverUser->hasRole('Conductor')) {
            $driverUser->assignRole($driverRole);
        }

        $this->call(MachineTypeSeeder::class);
        $this->call(MachineSeeder::class);
        $this->call(ProjectSeeder::class);
    }
}
