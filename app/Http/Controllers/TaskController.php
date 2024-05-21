<?php

namespace App\Http\Controllers;

use App\Services\KommoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $kommoService;

    public function __construct(KommoService $kommoService)
    {
        $this->kommoService = $kommoService;
    }

    public function index(Request $request)
    {
        $userId = $request->input('user_id');

        $tasks = $this->kommoService->getTasks($userId);

        return response()->json($tasks);
    }

    public function show($taskId)
    {
        $task = $this->kommoService->getTaskById($taskId);

        return response()->json($task);
    }

    public function checkTimeSlot(Request $request)
    {
        $userId = $request->input('user_id');
        $startTime = Carbon::parse($request->input('start_time'))->timestamp;
        $endTime = Carbon::parse($request->input('end_time'))->timestamp;

        if (!$this->kommoService->isTimeSlotAvailable($userId, $startTime, $endTime)) {
            return response()->json([
                'message' => 'The selected time slot is already occupied. Please choose another time.'
            ], 409);
        }

        return response()->json(['message' => 'The selected time slot is available.']);
    }
}
