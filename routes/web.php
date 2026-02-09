<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {

        // Vistas principales
        Route::get('/users', function () {
            return Inertia::render('Admin/Users');
        })->name('users');

        Route::get('/machines', function () {
            return Inertia::render('Admin/Machines');
        })->name('machines');

        // CRUD Usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/table', [UserController::class, 'table'])->name('table');
            Route::get('/add', [UserController::class, 'add'])->name('add');
            Route::get('/select/{id}', [UserController::class, 'select'])->name('select');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');

        });
              
        //CRUD MÁQUINAS (ADMIN)
        Route::prefix('machines')->name('machines.')->group(function () {

            // Endpoints CRUD
            Route::get('/table', [MachineController::class, 'table'])->name('table');
            Route::get('/add', [MachineController::class, 'add'])->name('add');
            Route::get('/select/{id}', [MachineController::class, 'select'])->name('select');
            Route::post('/create', [MachineController::class, 'create'])->name('create');
            Route::put('/update/{id}', [MachineController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [MachineController::class, 'delete'])->name('delete');
        });


    });


        //PERFIL USUARIO

        Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [UserController::class, 'destroy'])->name('profile.destroy');

        //MÁQUINAS 
        Route::prefix('machines')->name('machines.')->group(function () {
            Route::get('/table', [MachineController::class, 'table'])->name('table');
            Route::get('/add', [MachineController::class, 'add'])->name('add');
            Route::get('/select/{id}', [MachineController::class, 'select'])->name('select');
            Route::post('/create', [MachineController::class, 'create'])->name('create');
            Route::put('/update/{id}', [MachineController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [MachineController::class, 'delete'])->name('delete');
    });


});

require __DIR__.'/auth.php';
