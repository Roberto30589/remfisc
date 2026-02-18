<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    //ADMINISTRACIÓN solo para usuarios con rol "Administrador o Super-Administrador"
    Route::middleware('role:Administrador|Super-Administrador')->prefix('admin')->name('admin.')->group(function () {

        //Rutas Usuarios
        Route::get('users/table', [UserController::class, 'table'])->name('users.table');
        Route::resource('users', UserController::class)->except(['show']);
              
        //Rutas Máquinas
        Route::get('machines/table', [MachineController::class, 'table'])->name('machines.table');
        Route::resource('machines', MachineController::class)->except(['show']);
        
        //Rutas Obras
        Route::get('projects/table', [ProjectController::class, 'table'])->name('projects.table');
        Route::resource('projects', ProjectController::class)->except(['show']);

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',[AdminController::class, 'roleView'])->name('index');
            Route::get('/table',[AdminController::class, 'roleTable'])->name('table');
            Route::post('/create',[AdminController::class, 'createRole'])->name('create');
            Route::put('/{id}/update',[AdminController::class, 'updateRole'])->name('update');
        });

        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/',[AdminController::class, 'permissionView'])->name('index');
            Route::get('/table',[AdminController::class, 'permissionTable'])->name('table');
            Route::post('/create',[AdminController::class, 'createPermission'])->name('create');
            Route::put('/{id}/update',[AdminController::class, 'updatePermission'])->name('update');
        });
    });


    //Rutas Reportes Diarios (USUARIOS)
    Route::get('daily-reports/table', [DailyReportController::class, 'table'])->name('daily-reports.table');
    Route::resource('daily-reports', DailyReportController::class);

    //PERFIL USUARIO
    Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
