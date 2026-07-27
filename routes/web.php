<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingTargetController;
use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit']);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::resource('saving-targets', SavingTargetController::class)->except(['create', 'show', 'edit']);
    Route::resource('budgets', BudgetController::class)->except(['create', 'show', 'edit', 'update']);
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::resource('saving-targets', SavingTargetController::class);
    Route::post('/saving-targets/{savingTarget}/deposit', [SavingTargetController::class, 'deposit'])->name('saving-targets.deposit');
    Route::post('/saving-targets/{savingTarget}/withdraw', [SavingTargetController::class, 'withdraw'])->name('saving-targets.withdraw');
});

require __DIR__.'/auth.php';
