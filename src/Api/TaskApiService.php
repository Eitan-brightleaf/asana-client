<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Http\AsanaApiClient;

class TaskApiService
{
    private AsanaApiClient $client;

    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    public function getTasksByProject(string $projectId)
    {
        return $this->client->request('GET', "projects/{$projectId}/tasks");
    }

    public function createTask(array $data)
    {
        return $this->client->request('POST', 'tasks', [
            'json' => $data,
        ]);
    }
}