<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // USERS
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            //PROJECTS
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',

            // MACHINES
            'machines.view',
            'machines.create',
            'machines.edit',
            'machines.delete',

            // DAILY REPORTS
            'daily_reports.view',
            'daily_reports.view_all',
            'daily_reports.create',
            'daily_reports.edit',
            'daily_reports.edit_all',
            'daily_reports.delete',
            'daily_reports.delete_all',
            'daily_reports.finish',

            // ROLES
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // PERMISSIONS
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Creación de roles y asignación de permisos

        // Super Administrador
        $super = Role::firstOrCreate([
            'name' => 'Super-Administrador',
            'guard_name' => 'web'
        ]);

        $super->syncPermissions(Permission::all());


        // Administrador
        $admin = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web'
        ]);

        $admin->syncPermissions([
            // users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // projects
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',

            // machines
            'machines.view',
            'machines.create',
            'machines.edit',
            'machines.delete',

            // daily reports
            'daily_reports.view',
            'daily_reports.view_all',
            'daily_reports.create',
            'daily_reports.edit',
            'daily_reports.edit_all',
            'daily_reports.delete',
            'daily_reports.delete_all',
        ]);


        // Conductor
        $conductor = Role::firstOrCreate([
            'name' => 'Conductor',
            'guard_name' => 'web'
        ]);

        $conductor->syncPermissions([
            'daily_reports.view',
            'daily_reports.create',
            'daily_reports.edit',
        ]);
    }
}
