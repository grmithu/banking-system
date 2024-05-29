<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BankingController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});





Route::get('/deposit', [BankingController::class, 'showDepositForm'])->name('deposit.form');
Route::post('/deposit', [BankingController::class, 'deposit'])->name('deposit');
Route::get('/withdraw', [BankingController::class, 'showWithdrawForm'])->name('withdraw.form');
Route::post('/withdraw', [BankingController::class, 'withdraw'])->name('withdraw');


