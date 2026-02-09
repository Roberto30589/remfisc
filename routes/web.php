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

        // CRUD Usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',[UserController::class, 'index'])->name('index');
            Route::get('/table', [UserController::class, 'table'])->name('table');
            Route::get('/add', [UserController::class, 'add'])->name('add');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        });
              
        //CRUD MÁQUINAS (ADMIN)
        Route::prefix('machines')->name('machines.')->group(function () {            
            Route::get('/',[MachineController::class, 'index'])->name('index');
            Route::get('/table', [MachineController::class, 'table'])->name('table');
            Route::get('/add', [MachineController::class, 'add'])->name('add');
            Route::get('/edit/{id}', [MachineController::class, 'edit'])->name('edit');
            Route::post('/create', [MachineController::class, 'create'])->name('create');
            Route::put('/update/{id}', [MachineController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [MachineController::class, 'delete'])->name('delete');
        });


    });

    //PERFIL USUARIO
    Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
