<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TripsController;
use App\Http\Controllers\TripDayController;
use App\Http\Controllers\ScheduleItemController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RegionController;  


/**
 * Trips API Routes
 */
Route::apiResource('trips', TripsController::class);

/**
 * Trip Days API Routes
 */
Route::apiResource('trip.days', TripDayController::class)->shallow();

/**
 * Schedule Items API Routes
 */
Route::apiResource('days.items', ScheduleItemController::class)->shallow();