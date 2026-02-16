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
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('users/table', [UserController::class, 'table'])->name('users.table');
              
        //Rutas Máquinas
        Route::resource('machines', MachineController::class)->except(['show']);
        Route::get('machines/table', [MachineController::class, 'table'])->name('machines.table');
        
        //Rutas Obras
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::get('projects/table', [ProjectController::class, 'table'])->name('projects.table');

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
    Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
        //vistas
        Route::get('/', [DailyReportController::class, 'index'])->name('index');
        Route::get('/table', [DailyReportController::class, 'table'])->name('table');
        Route::get('/add', [DailyReportController::class, 'add'])->name('add');
        Route::get('/edit/{id}', [DailyReportController::class, 'edit'])->name('edit');
        Route::get('/report/{id}', [DailyReportController::class, 'report'])->name('report');
        //CRUD
        Route::post('/create', [DailyReportController::class, 'create'])->name('create');
        Route::put('/update/{id}', [DailyReportController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [DailyReportController::class, 'destroy'])->name('destroy');
    });


    //PERFIL USUARIO
    Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
