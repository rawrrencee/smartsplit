<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/home', function () {
        return Inertia::render('Home');
    })->name('home');

    Route::get('/groups', function () {
        return Inertia::render('Groups');
    })->name('groups');

    Route::get('/view-group', function () {
        return Inertia::render('ViewGroup');
    })->name('view-group');

    Route::get('/create-edit-group', function () {
        return Inertia::render('CreateOrEditGroup');
    })->name('create-edit-group');

    Route::get('/settle-up', function () {
        return Inertia::render('SettleUp');
    })->name('settle-up');

    Route::get('/add-new-expense', function () {
        return Inertia::render('AddNewExpense');
    })->name('add-new-expense');

    Route::get('/expense-details', function () {
        return Inertia::render('ExpenseDetails');
    })->name('expense-details');
});
