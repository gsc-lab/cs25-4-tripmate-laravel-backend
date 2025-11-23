<?php

namespace App\Http\Controllers\ScheduleItem;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\ScheduleItem\ScheduleItemIndexRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemStoreRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemUpdateRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemReorderRequest;
use App\Http\Resources\ScheduleItemResource;

use App\Services\Trip\TripService;
use App\Services\ScheduleItem\ScheduleItemService;


class ScheduleItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}