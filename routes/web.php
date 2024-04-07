<?php

use App\Http\Controllers\CommonController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupController;
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
    Route::get('/photo', [CommonController::class, 'showPhoto'])->name('photo');

    Route::get('/home', function () {
        return Inertia::render('Home');
    })->name('home');

    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index'])->name('groups');
        Route::post('/add', [GroupController::class, 'store'])->name('groups.add');
        Route::post('/add-member', [GroupController::class, 'addMember'])->name('groups.add.member');
        Route::post('/remove-member', [GroupController::class, 'removeMember'])->name('groups.remove.member');
        Route::get('/view-group', [GroupController::class, 'view'])->name('groups.view');
    });

    Route::prefix('group-members')->group(function () {
        Route::post('/update', [GroupController::class, 'updatePendingGroupRequestStatus'])->name('group-members.update');
    });

    Route::prefix('expenses')->group(function () {
        Route::get('/add', [ExpenseController::class, 'addExpensePage'])->name('expenses.add');
    });

    Route::get('/edit-group', function () {
        return Inertia::render('EditGroup');
    })->name('edit-group');

    Route::get('/settle-up', function () {
        return Inertia::render('SettleUp');
    })->name('settle-up');



    Route::get('/expense-details', function () {
        return Inertia::render('ExpenseDetails');
    })->name('expense-details');
});
