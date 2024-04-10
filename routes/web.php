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

Route::get('/404', function () {
    return Inertia::render('Error/404');
})->name('404');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/photo', [CommonController::class, 'showPhoto'])->name('photo');
    Route::get('/currencies', [CommonController::class, 'getCurrencies'])->name('currencies');

    Route::get('/home', function () {
        return Inertia::render('Home');
    })->name('home');

    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index'])->name('groups');
        Route::post('/add', [GroupController::class, 'store'])->name('groups.add');
        Route::get('/view', [GroupController::class, 'view'])->name('groups.view');
        Route::get('/edit', [GroupController::class, 'edit'])->name('groups.edit');
        Route::post('/update', [GroupController::class, 'update'])->name('groups.update');
        Route::post('/delete-photo', [GroupController::class, 'deletePhoto'])->name('groups.delete-photo');
    });

    Route::prefix('group-members')->group(function () {
        Route::post('/add', [GroupController::class, 'addMember'])->name('groups-members.add');
        Route::post('/update', [GroupController::class, 'updatePendingGroupRequestStatus'])->name('group-members.update');
        Route::post('/remove', [GroupController::class, 'removeMember'])->name('groups-members.remove');
    });

    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'addExpensePage'])->name('expenses');
        Route::post('/add', [ExpenseController::class, 'saveNewExpense'])->name('expenses.add');
    });
    Route::prefix('settle-up')->group(function () {
        Route::get('/', [ExpenseController::class, 'settleUpPage'])->name('settle-up');
    });



    Route::get('/expense-details', function () {
        return Inertia::render('ExpenseDetails');
    })->name('expense-details');
});
