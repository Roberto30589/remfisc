<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DailyReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    //ADMINISTRACIÓN
    Route::prefix('admin')->name('admin.')->group(function () {
        //Rutas Usuarios (ADMIN)
        Route::prefix('users')->name('users.')->group(function () {
            //vistas
            Route::get('/',[UserController::class, 'index'])->name('index');
            Route::get('/table', [UserController::class, 'table'])->name('table');
            Route::get('/add', [UserController::class, 'add'])->name('add');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
            //CRUD
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        });
              
        //Rutas Máquinas (ADMIN)
        Route::prefix('machines')->name('machines.')->group(function () {            
            //vistas
            Route::get('/',[MachineController::class, 'index'])->name('index');
            Route::get('/table', [MachineController::class, 'table'])->name('table');
            Route::get('/add', [MachineController::class, 'add'])->name('add');
            Route::get('/edit/{id}', [MachineController::class, 'edit'])->name('edit');
            //CRUD
            Route::post('/create', [MachineController::class, 'create'])->name('create');
            Route::put('/update/{id}', [MachineController::class, 'update'])->name('update');
            Route::delete('/delete/{id}', [MachineController::class, 'destroy'])->name('destroy');
        });
    });

    //Rutas Obras (USUARIOS)
    Route::prefix('projects')->name('projects.')->group(function () {
        //vistas
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/table', [ProjectController::class, 'table'])->name('table');
        Route::get('/add', [ProjectController::class, 'add'])->name('add');
        Route::get('/edit/{id}', [ProjectController::class, 'edit'])->name('edit');
        //CRUD
        Route::post('/create', [ProjectController::class, 'create'])->name('create');
        Route::put('/update/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [ProjectController::class, 'destroy'])->name('destroy');
    });

    //Rutas Reportes Diarios (USUARIOS)
    Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
        //vistas
        Route::get('/', [DailyReportController::class, 'index'])->name('index');
        Route::get('/table', [DailyReportController::class, 'table'])->name('table');
        Route::get('/add', [DailyReportController::class, 'add'])->name('add');
        Route::get('/show/{id}', [DailyReportController::class, 'show'])->name('show');
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
