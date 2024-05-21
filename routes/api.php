<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/tasks', [TaskController::class, 'index']);
Route::get('/tasks/{taskId}', [TaskController::class, 'show']);
Route::post('/tasks/check-time-slot', [TaskController::class, 'checkTimeSlot']);
