<?php

use App\Http\Controllers\AmbulanceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

//Route Ambulance
Route::apiResource('/ambulances', AmbulanceController::class, [
    'only' => ['index', 'show'],
]);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [UserController::class, 'logout']);

    // Route Booking for User
    Route::apiResource('/bookings', BookingController::class, [
        'except' => ['destroy'],
    ]);


    //ROUTE FOR DRIVER
    Route::middleware('api.driver')->group(function () {
        // Route Ambulance
        Route::apiResource('/ambulances', AmbulanceController::class, [
            'only' => ['store', 'update'],
        ]);
        
    });

    

});
