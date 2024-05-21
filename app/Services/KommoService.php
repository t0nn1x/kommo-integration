<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KommoService
{
    protected $baseUri;
    protected $token;

    public function __construct()
    {
        $this->baseUri = config('services.kommo.base_uri');
        $this->token = config('services.kommo.token');
    }

    public function getTasks($userId = null, $filters = [], $order = [])
    {
        $queryParams = array_merge([
            'page' => 1,
            'limit' => 250,
        ], $filters, $order);

        if ($userId) {
            $queryParams['filter[responsible_user_id]'] = $userId;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get($this->baseUri . '/api/v4/tasks', $queryParams);

        if ($response->status() == 204) {
            return collect([]);
        }

        if ($response->failed()) {
            Log::error('Failed to fetch tasks from Kommo', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('Failed to fetch tasks from Kommo: ' . $response->body());
        }

        Log::info('Kommo API response', ['status' => $response->status(), 'body' => $response->body()]);

        $data = $response->json();

        if (!isset($data['_embedded']['tasks'])) {
            Log::error('Invalid response format', ['response' => $data]);
            throw new \Exception('Invalid response format from Kommo API');
        }

        return collect($data['_embedded']['tasks'])->map(function ($taskData) {
            return new Task($taskData);
        });
    }

    public function getTaskById($taskId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get($this->baseUri . '/api/v4/tasks/' . $taskId);

        if ($response->status() == 204) {
            return null;
        }

        $data = $response->json();

        if ($response->failed()) {
            Log::error('Failed to fetch task from Kommo', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('Failed to fetch task from Kommo: ' . $response->body());
        }

        return new Task($data);
    }

    public function isTimeSlotAvailable($userId, $startTime, $endTime)
    {
        $filters = [
            'filter[responsible_user_id]' => $userId,
            'filter[is_completed]' => 0,
        ];

        $tasks = $this->getTasks($userId, $filters);

        foreach ($tasks as $task) {
            $taskDuration = $task->duration == 0 ? 1800 : $task->duration;
            $taskStart = $task->complete_till - $taskDuration;
            $taskEnd = $task->complete_till;

            // Логування для дебагу
            Log::info('Task Time Check', [
                'task_id' => $task->id,
                'task_start' => Carbon::createFromTimestamp($taskStart)->toDateTimeString(),
                'task_end' => Carbon::createFromTimestamp($taskEnd)->toDateTimeString(),
                'check_start' => Carbon::createFromTimestamp($startTime)->toDateTimeString(),
                'check_end' => Carbon::createFromTimestamp($endTime)->toDateTimeString()
            ]);

            if (($startTime < $taskEnd) && ($endTime > $taskStart)) {
                Log::info('Time slot conflict found', ['task_id' => $task->id]);
                return false;
            }
        }

        return true;
    }
}
