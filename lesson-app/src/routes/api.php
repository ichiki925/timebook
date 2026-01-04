<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\LessonSlotController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\StudentReservationController;


// レッスン枠一覧を取得（ログイン前でも見れる必要がある）
Route::get('reservations/available-slots', [ReservationController::class, 'getAvailableSlots']);

// 管理者用認証API
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// 生徒会員用認証API
Route::post('/student/register', [StudentAuthController::class, 'register']);
Route::post('/student/login', [StudentAuthController::class, 'login']);

// 認証が必要なエンドポイント
Route::middleware('auth:sanctum')->group(function () {

    // 管理者用
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // 生徒会員用
    Route::post('/student/logout', [StudentAuthController::class, 'logout']);
    Route::get('/student/user', [StudentAuthController::class, 'user']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/today-reservations', [DashboardController::class, 'getTodayReservations']);
        Route::get('/week-reservations', [DashboardController::class, 'getWeekReservations']);
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/next-reservation', [DashboardController::class, 'getNextReservation']);
        Route::get('/month-reservations', [DashboardController::class, 'getMonthReservations']);
    });

    Route::prefix('lesson-slots')->group(function () {
        Route::get('/', [LessonSlotController::class, 'index']);
        Route::post('/', [LessonSlotController::class, 'store']);
        Route::put('/{id}', [LessonSlotController::class, 'update']);
        Route::delete('/{id}', [LessonSlotController::class, 'destroy']);
        Route::post('/bulk', [LessonSlotController::class, 'bulkStore']);
    });

    // 管理者用: 予約管理
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);

    Route::prefix('student')->group(function () {
        Route::get('/reservations', [StudentReservationController::class, 'index']);
        Route::post('/reservations', [StudentReservationController::class, 'store']); // ← 会員のみ予約可能
        Route::get('/reservations/{id}', [StudentReservationController::class, 'show']);
        Route::delete('/reservations/{id}', [StudentReservationController::class, 'destroy']);
    });
});



