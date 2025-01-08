<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\BookingController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Admin Dashboard';
    });

    Route::post('/v1/vehicles', [VehicleController::class, 'addVehicle']);
});

Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::get('/customer/dashboard', function () {
        return 'Customer Dashboard';
    });
    Route::get('/v1/vehicles', [VehicleController::class, 'listAvailableVehicles']);
    Route::post('/v1/bookings', [BookingController::class, 'createBooking']);
    Route::get('/v1/bookings/{booking}', [BookingController::class, 'getBookingDetails']);
});