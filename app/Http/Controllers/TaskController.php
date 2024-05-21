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
}
