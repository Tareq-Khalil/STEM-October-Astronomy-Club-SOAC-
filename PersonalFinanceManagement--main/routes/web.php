<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\HomeComponent;
use App\Livewire\AccountComponent;
use App\Livewire\AccountShowComponent;
use App\Livewire\TransactionComponent;
use App\Livewire\EditTransactionComponent;
use App\Livewire\NavbarAccountDropdown;
use App\Http\Controllers\ProfileController;
use App\Livewire\EditAccountComponent;

// Welcome Route
Route::get('/', function () {
    return view('welcome');
});

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Home Route
    Route::get('/home', HomeComponent::class)->name('home');

    // Account Routes
    Route::get('/accounts', AccountComponent::class)->name('accounts.index');
    Route::get('/accounts/{account}', AccountShowComponent::class)->name('accounts.show');
    Route::get('/accounts/{account}/edit', EditAccountComponent::class)->name('accounts.edit');

    // Transaction Routes
    Route::get('/transactions', TransactionComponent::class)->name('transactions.index');
// Transaction Routes
Route::get('/transactions/{transactionId}/edit', EditTransactionComponent::class)->name('transactions.edit');
    Route::get('/navbar-account-dropdown', NavbarAccountDropdown::class)->name('navbar-account-dropdown');
});

// Profile Routes (Authenticated Only)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Logout Route
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// Authentication Routes (Login, Register, Password Reset, etc.)
require __DIR__.'/auth.php';