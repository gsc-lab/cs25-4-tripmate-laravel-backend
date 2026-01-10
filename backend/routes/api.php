<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UsersController;
use App\Http\Controllers\Place\PlaceController;
use App\Http\Controllers\Region\RegionController;
use App\Http\Controllers\Trip\ScheduleItemController;
use App\Http\Controllers\Trip\TripController;
use App\Http\Controllers\Trip\TripDayController;
use Illuminate\Support\Facades\Route;

// API Version 2
Route::prefix('v2')->group(function () {

    // 안중 불필요 공개 API (회원가입 및 로그인)
    Route::post('/users', [AuthController::class, 'registerUser']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // // 인증된 사용자만 접근 가능한 API
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
         * Trip
         * GET         /v2/trips
         * POST        /v2/trips
         * GET         /v2/trips/{trip}
         * PUT/PATCH   /v2/trips/{trip}
         * DELETE      /v2/trips/{trip}
         */
        Route::apiResource('trips', TripController::class);

        /**
         * Trip Days
         * GET    /v2/trips/{trip_id}/days                 목록
         * POST   /v2/trips/{trip_id}/days                 생성
         * GET    /v2/trips/{trip_id}/days/{day_no}        단건 조회
         * PATCH  /v2/trips/{trip_id}/days/{day_no}        메모 수정
         * DELETE /v2/trips/{trip_id}/days/{day_no}        삭제
         * POST   /v2/trips/{trip_id}/days/reorder         순서 변경
         */
        Route::get('/trips/{trip_id}/days', [TripDayController::class, 'index']);
        Route::post('/trips/{trip_id}/days', [TripDayController::class, 'store']);
        Route::get('/trips/{trip_id}/days/{day_no}', [TripDayController::class, 'show']);
        Route::patch('/trips/{trip_id}/days/{day_no}', [TripDayController::class, 'updateMemo']);
        Route::delete('/trips/{trip_id}/days/{day_no}', [TripDayController::class, 'destroy']);
        Route::post('/trips/{trip_id}/days/reorder', [TripDayController::class, 'reorder']);

        /**
         * Schedule Items
         * GET    /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items                       목록
         * POST   /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items                       생성
         * GET    /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}    단건 조회
         * PATCH  /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}    부분 수정
         * PUT    /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}    전체 수정
         * DELETE /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}    삭제
         * POST   /v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/reorder               재배치
         */
        Route::get('/trips/{trip_id}/days/{trip_day_id}/schedule-items', [ScheduleItemController::class, 'index']);
        Route::post('/trips/{trip_id}/days/{trip_day_id}/schedule-items', [ScheduleItemController::class, 'store']);
        Route::get('/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}', [ScheduleItemController::class, 'show']);
        Route::patch('/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}', [ScheduleItemController::class, 'patch']);
        Route::put('/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}', [ScheduleItemController::class, 'put']);
        Route::delete('/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}', [ScheduleItemController::class, 'destroy']);
        Route::post('/trips/{trip_id}/days/{trip_day_id}/schedule-items/reorder', [ScheduleItemController::class, 'reorder']);


        /**
         * Places
         * GET    /v2/places/autocomplete
         * GET    /v2/places/external-search
         * GET    /v2/places/reverse-geocode
         * GET    /v2/places/place-geocode
         * GET    /v2/places/nearby
         * POST   /v2/places/from-external
         * GET    /v2/places/{place_id}
         */
        Route::get('/places/autocomplete', [PlaceController::class, 'autocomplete']);
        Route::get('/places/external-search', [PlaceController::class, 'externalSearch']);
        Route::get('/places/reverse-geocode', [PlaceController::class, 'reverseGeocode']);
        Route::get('/places/place-geocode', [PlaceController::class, 'placeGeocode']);
        Route::get('/places/nearby', [PlaceController::class, 'nearbyPlaces']);
        Route::post('/places/from-external', [PlaceController::class, 'createPlaceFromExternal']);
        Route::get('/places/{place_id}', [PlaceController::class, 'getPlaceById']);

        /**
         * Regions
         * GET  /v2/regions
         */
        Route::get('/regions', [RegionController::class, 'listRegions']);
    });
});