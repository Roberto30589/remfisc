<?php

use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});


Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {return Inertia::render('Dashboard');})->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', function () {return Inertia::render('Admin/Users');})->name('users');
        Route::get('/machines', function () {return Inertia::render('Admin/Machines');})->name('machines');
    });


    Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
