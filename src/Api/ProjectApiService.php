<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Http\AsanaApiClient;

class ProjectApiService
{
    private AsanaApiClient $client;

    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    public function getProject(string $projectId)
    {
        return $this->client->request('GET', "projects/{$projectId}");
    }

    public function getAllProjects()
    {
        return $this->client->request('GET', 'projects');
    }
}