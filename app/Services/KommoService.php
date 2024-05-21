<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $data = $response->json();

        if ($response->failed()) {
            // Обробка помилок
            throw new \Exception('Failed to fetch tasks from Kommo: ' . $response->body());
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

        $data = $response->json();

        if ($response->failed()) {
            // Логуємо відповідь сервера
            Log::error('Failed to fetch task from Kommo: ' . $response->body());

            // Обробка помилок
            throw new \Exception('Failed to fetch task from Kommo: ' . $response->body());
        }

        return new Task($data);
    }
}
