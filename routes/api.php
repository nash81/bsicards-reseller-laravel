<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\WithdrawController;

/*
|--------------------------------------------------------------------------
| Mobile App API Routes  (prefix: /api/v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ------------------------------------------------------------------
    // Public – Authentication
    // ------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('login',    [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });

    // ------------------------------------------------------------------
    // Protected – Sanctum token required
    // ------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout',          [AuthController::class, 'logout']);
            Route::get('me',               [AuthController::class, 'me']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // Profile & Balance
        Route::prefix('profile')->group(function () {
            Route::get('/',                    [ProfileController::class, 'index']);
            Route::post('update',              [ProfileController::class, 'update']);
            Route::get('balance',              [ProfileController::class, 'balance']);
            Route::get('recent-transactions',  [ProfileController::class, 'recentTransactions']);
        });

        // Transactions
        Route::prefix('transactions')->group(function () {
            Route::get('/',           [TransactionController::class, 'index']);
            Route::get('deposits',    [TransactionController::class, 'deposits']);
            Route::get('withdrawals', [TransactionController::class, 'withdrawals']);
            Route::get('{tnx}',       [TransactionController::class, 'show']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('{id}/read', [NotificationController::class, 'read']);
            Route::post('read-all', [NotificationController::class, 'readAll']);
        });

        // Deposits / Payment Gateways
        Route::prefix('deposit')->group(function () {
            Route::get('gateways',                [DepositController::class, 'gateways']);
            Route::post('initiate',               [DepositController::class, 'initiate']);
            Route::post('manual-proof',           [DepositController::class, 'submitManualProof']);
            Route::get('status/{tnx}',            [DepositController::class, 'status']);
        });

        // Withdrawals
        Route::prefix('withdraw')->group(function () {
            Route::get('methods',                 [WithdrawController::class, 'methods']);
            Route::get('accounts',                [WithdrawController::class, 'accounts']);
            Route::post('accounts',               [WithdrawController::class, 'createAccount']);
            Route::post('accounts/{id}',          [WithdrawController::class, 'updateAccount']);
            Route::delete('accounts/{id}',        [WithdrawController::class, 'deleteAccount']);
            Route::get('details',                 [WithdrawController::class, 'details']);
            Route::post('initiate',               [WithdrawController::class, 'initiate']);
            Route::get('status/{tnx}',            [WithdrawController::class, 'status']);
        });

        Route::get('cards/fees', [CardController::class, 'fees']);


/**
 * Define routes for card operations
 * This function sets up various endpoints for managing card-related functionalities
 * including listing, viewing, loading funds, and blocking/unblocking cards
 */


        // Virtual Cards – Digital Mastercard
        Route::prefix('cards/digital')->group(function () {
            Route::get('/',                       [CardController::class, 'digitalList']);
            Route::post('apply',                  [CardController::class, 'digitalApply']);
            Route::post('addon',                  [CardController::class, 'digitalAddon']);
            Route::get('{cardId}',                [CardController::class, 'digitalView']);
            Route::post('load',                   [CardController::class, 'digitalLoadFunds']);
            Route::post('{cardId}/block',         [CardController::class, 'digitalBlock']);
            Route::post('{cardId}/unblock',       [CardController::class, 'digitalUnblock']);
            Route::get('{cardId}/check-3ds',      [CardController::class, 'check3ds']);
            Route::post('{cardId}/approve-3ds',   [CardController::class, 'approve3ds']);
            Route::get('{cardId}/wallet-otp',     [CardController::class, 'checkWalletOtp']);
        });

        // Virtual Cards – Digital Visa Wallet
        Route::prefix('cards/digitalvisa')->group(function () {
            Route::get('/',                       [CardController::class, 'digitalvisaList']);
            Route::post('apply',                  [CardController::class, 'digitalvisaApply']);
            // Changed endpoint for Digital Visa Wallet card details
            Route::get('digitalvisagetcard/{cardId}', [CardController::class, 'digitalvisaView']);
            Route::post('load',                   [CardController::class, 'digitalvisaLoadFunds']);
            Route::post('{cardId}/block',         [CardController::class, 'digitalvisaBlock']);
            Route::post('{cardId}/unblock',       [CardController::class, 'digitalvisaUnblock']);
            Route::get('checkotp/{cardId}',     [CardController::class, 'digitalvisaCheckOtp']);
        });

    });

});
