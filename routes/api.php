<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/admin/create-user', [AdminController::class, 'createUser']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/search-user', [AdminController::class, 'searchUser']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/users-by-month/{year?}', [AdminController::class, 'getUsersCountByMonth']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/profile', [AdminController::class, 'getProfile']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/record-payment', [AdminController::class, 'recordPayment']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/payments', [AdminController::class, 'getAllPayments']);
});



Route::get('/users/download-pdf', [AdminController::class, 'downloadUsersPDF']);
//Route::get('/users/download-excel', [AdminController::class, 'downloadUsersExcel']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/monthly-payment-counts', [AdminController::class, 'getMonthlyPaymentCounts']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/dashboard-stats', [AdminController::class, 'getDashboardStats']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/all', [AdminController::class, 'getAllRegisteredAdmins']);
});



Route::middleware('auth:sanctum')->get('/admin/user/{id}', [AdminController::class, 'getUserProfile']);

Route::post('/admin/login', [AdminController::class, 'login']);


 Route::post('/admin/register', [AdminController::class, 'register']);