<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // ✔ import correcto
use Spatie\Permission\Models\Permission; // ✔ import correcto

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            //users
            ['name' => 'users.view','guard_name'=>'web'],
            ['name' => 'users.create','guard_name'=>'web'],
            ['name' => 'users.edit','guard_name'=>'web'],
            ['name' => 'users.delete','guard_name'=>'web'],

            //projects
            ['name' => 'projects.view','guard_name'=>'web'],
            ['name' => 'projects.create','guard_name'=>'web'],
            ['name' => 'projects.edit','guard_name'=>'web'],
            ['name' => 'projects.delete','guard_name'=>'web'],

            //machines
            ['name' => 'machines.view','guard_name'=>'web'],
            ['name' => 'machines.create','guard_name'=>'web'],
            ['name' => 'machines.edit','guard_name'=>'web'],
            ['name' => 'machines.delete','guard_name'=>'web'],

            //Daily Reports
            ['name' => 'daily_reports.view','guard_name'=>'web'],
            ['name' => 'daily_reports.create','guard_name'=>'web'],
            ['name' => 'daily_reports.edit','guard_name'=>'web'],
            ['name' => 'daily_reports.delete','guard_name'=>'web'],

            //roles
            ['name' => 'roles.view','guard_name'=>'web'],
            ['name' => 'roles.create','guard_name'=>'web'],
            ['name' => 'roles.edit','guard_name'=>'web'],
            ['name' => 'roles.delete','guard_name'=>'web'],

            //permissions
            ['name' => 'permissions.view','guard_name'=>'web'],
            ['name' => 'permissions.create','guard_name'=>'web'],
            ['name' => 'permissions.edit','guard_name'=>'web'],
            ['name' => 'permissions.delete','guard_name'=>'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $super = Role::create(['name' => 'Super-Administrador','guard_name'=>'web']);
        $super->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'Administrador','guard_name'=>'web']);
        $admin->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete', 
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'machines.view',
            'machines.create',
            'machines.edit',
            'machines.delete',
            'daily_reports.view',
            'daily_reports.create',
            'daily_reports.edit',
            'daily_reports.delete',
        ]);

        $conductor = Role::firstOrCreate(['name' => 'Conductor']);
        $conductor->givePermissionTo([
            'daily_reports.view',
            'daily_reports.create',
            'daily_reports.edit',
        ]);
    }
}
