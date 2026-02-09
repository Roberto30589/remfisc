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
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        $this->call(RoleSeeder::class);

        $adminRole  = Role::where('name', 'administrador')->first();
        $driverRole = Role::where('name', 'conductor')->first();

        /*
        |--------------------------------------------------------------------------
        | Usuario Administrador de prueba
        |--------------------------------------------------------------------------
        */
        $adminUser = User::firstOrCreate(
            ['rut' => '19.874.109-4'], // RUT único
            [
                'name'     => 'Administrador',
                'email'    => 'matias18698@gmail.com',
                'password' => Hash::make('123456789'),
            ]
        );

        if (! $adminUser->hasRole('administrador')) {
            $adminUser->assignRole($adminRole);
        }

        /*
        |--------------------------------------------------------------------------
        | (Opcional) Usuario Conductor de prueba
        |--------------------------------------------------------------------------
        */
        $driverUser = User::firstOrCreate(
        ['rut' => '11.111.111-9'], // RUT válido
        [
            'name'     => 'Conductor Prueba',
            'email'    => 'conductor@gmail.com',
            'password' => Hash::make('123456789'),
        ]
      );

        if (! $driverUser->hasRole('conductor')) {
            $driverUser->assignRole($driverRole);
        }
    }
}
