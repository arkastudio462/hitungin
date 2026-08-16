<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationForwardController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/notification-forward', [NotificationForwardController::class, 'store'])
    ->middleware('android_api_key');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('budgets', BudgetController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('recurring-transactions', RecurringTransactionController::class);
    Route::apiResource('savings-goals', SavingsGoalController::class);

    Route::post('/savings-goals/{savingsGoal}/deposit', [SavingsGoalController::class, 'deposit']);

    Route::post('/receipts/scan', [ReceiptController::class, 'scan']);
    Route::post('/receipts/save', [ReceiptController::class, 'save']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/by-category', [ReportController::class, 'byCategory']);
    Route::get('/reports/trend', [ReportController::class, 'trend']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/notification-forwards', [NotificationForwardController::class, 'index']);
    Route::get('/notification-forwards/pending-count', [NotificationForwardController::class, 'pendingCount']);
    Route::post('/notification-forwards/{notificationForward}/confirm', [NotificationForwardController::class, 'confirm']);
    Route::post('/notification-forwards/{notificationForward}/ignore', [NotificationForwardController::class, 'ignore']);
});
