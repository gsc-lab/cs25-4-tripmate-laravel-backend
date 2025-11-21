<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UsersController;
use App\Http\Controllers\Trip\TripsController;
use App\Http\Controllers\TripDay\TripDayController;
use App\Http\Controllers\ScheduleItem\ScheduleItemController;
use App\Http\Controllers\Place\PlaceController;
use App\Http\Controllers\Region\RegionController;  

// API Version 2
Route::prefix('v2')->group(function () {
  
  // 안중 불필요 공개 API (회원가입 및 로그인)
  Route::post('/users', [AuthController::class, 'registerUser']);   
  Route::post('/auth/login', [AuthController::class, 'login']);     


  // 인증된 사용자만 접근 가능한 API
  Route::middleware('auth:sanctum')->group(function () {

    /**
     * Users
     * GET      /v2/users/me
     * DELETE   /v2/users/me
     */
    Route::get('/users/me', [UsersController::class, 'getCurrentUser']);
    Route::delete('/users/me', [UsersController::class, 'deleteCurrentUser']);

    /**
     * Auth
     * POST /v2/auth/logout
     */
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    /**
     * Trips
     * GET              /v2/trips
     * POST             /v2/trips
     * GET/PUT/DELETE   /v2/trips/{trip_id}
     */
    Route::apiResource('trips', TripsController::class);

    /**
     * Trip Days (Nested + shallow)
     * GET              /v2/trips/{trip_id}/days
     * POST             /v2/trips/{trip_id}/days
     * POST             /v2/trips/{trip_id}/days:reorder
     * GET/PUT/DELETE   /v2/trips/{trip_id}/days/{day_no}
     */
    Route::apiResource('trips.days', TripDayController::class)->shallow();
    Route::post('/trips/{trip}/days/reorder', [TripDayController::class, 'reorder']);

    /**
     * Schedule Items (Nested + shallow)
     * GAT/POST       /v2/trips/{trip_id}/days/{day_no}/items
     * PATCH/DELETE   /v2/trips/{trip_id}/days/{day_no}/items/{item_id}
     * POST           /v2/trips/{trip_id}/days/{day_no}/items:reorder
     */
    Route::apiResource('days.items', ScheduleItemController::class)->shallow();
    Route::post('/days/{day}/items/reorder', [ScheduleItemController::class, 'reorder']);

    /**
     * Places
     * GET    /v2/places/external-search
     * GET    /v2/places/{place_id}
     * GET    /v2/places/reverse-geocode
     * GET    /v2/places/place-geocode
     * GET    /v2/places/nearby
     * POST   /v2/places/from-external
     */
    Route::get('/places/external-search', [PlaceController::class, 'externalSearch']);
    Route::get('/places/{place}', [PlaceController::class, 'getPlaceById']);
    Route::get('/places/reverse-geocode', [PlaceController::class, 'reverseGeocode']);
    Route::get('/places/place-geocode', [PlaceController::class, 'placeGeocode']);
    Route::get('/places/nearby', [PlaceController::class, 'nearbyPlaces']);
    Route::post('/places/from-external', [PlaceController::class, 'createPlaceFromExternal']);

    /**
     * Regions
     * GET  /v2/regions
     */
    Route::get('/regions', [RegionController::class, 'listRegions']);
  });
});