<?php


use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// User APIs
Route::post('/users', [UserController::class, 'store']); // Create user
Route::get('/users/{id}', [UserController::class, 'show']); // Get user details

// Wallet APIs
Route::post('/wallets/{userId}/deposit', [WalletController::class, 'deposit']); // Deposit funds
Route::post('/wallets/{userId}/withdraw', [WalletController::class, 'withdraw']); // Withdraw funds

// Transaction APIs
Route::post('/transactions/transfer', [TransactionController::class, 'transfer']); // Transfer funds
Route::get('/users/{userId}/transactions', [TransactionController::class, 'index']); // List user transactions