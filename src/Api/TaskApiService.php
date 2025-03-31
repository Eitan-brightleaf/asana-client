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

	/**
	 * Get tasks for a specific project
	 *
	 * @param string $projectId The project ID
	 * @param array $options Optional parameters (opt_fields, limit, offset)
	 * @return array
	 */
	public function getTasksByProject(string $projectId, array $options = []): array {
		return $this->client->request('GET', "projects/$projectId/tasks", [
			'query' => $options
		]);
	}

	/**
	 * Get a specific task by ID
	 *
	 * @param string $taskId The task ID
	 * @param array $options Optional parameters (opt_fields)
	 * @return array
	 */
	public function getTask(string $taskId, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskId", [
			'query' => $options
		]);
	}

	/**
	 * Create a new task
	 *
	 * @param array $data Task data
	 * @param array $options Optional parameters (opt_fields)
	 * @return array
	 */
	public function createTask(array $data, array $options = []): array {
		return $this->client->request('POST', 'tasks', [
			'json' => $data,
			'query' => $options
		]);
	}

	/**
	 * Update an existing task
	 *
	 * @param string $taskId The task ID
	 * @param array $data The data to update
	 * @param array $options Optional parameters (opt_fields)
	 * @return array
	 */
	public function updateTask(string $taskId, array $data, array $options = []): array {
		return $this->client->request('PUT', "tasks/$taskId", [
			'json' => $data,
			'query' => $options
		]);
	}

	/**
	 * Delete a task
	 *
	 * @param string $taskId The task ID
	 * @return array
	 */
	public function deleteTask(string $taskId): array {
		return $this->client->request('DELETE', "tasks/$taskId");
	}

	/**
	 * Get subtasks for a specific task
	 *
	 * @param string $taskId The parent task ID
	 * @param array $options Optional parameters (opt_fields, limit, offset)
	 * @return array
	 */
	public function getSubtasks(string $taskId, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskId/subtasks", [
			'query' => $options
		]);
	}

	/**
	 * Add a task to a project
	 *
	 * @param string $taskId The task ID
	 * @param string $projectId The project ID
	 * @param array $data Additional parameters (section, insert_before, insert_after)
	 * @return array
	 */
	public function addProjectToTask(string $taskId, string $projectId, array $data = []): array {
		$data['project'] = $projectId;
		return $this->client->request('POST', "tasks/$taskId/addProject", [
			'json' => $data
		]);
	}

	/**
	 * Remove a task from a project
	 *
	 * @param string $taskId The task ID
	 * @param string $projectId The project ID
	 * @return array
	 */
	public function removeProjectFromTask(string $taskId, string $projectId): array {
		return $this->client->request('POST', "tasks/$taskId/removeProject", [
			'json' => [
				'project' => $projectId
			]
		]);
	}

	/**
	 * Set the parent of a task
	 *
	 * @param string $taskId The task ID
	 * @param string $parentId The parent task ID
	 * @param array $options Optional parameters (opt_fields)
	 * @return array
	 */
	public function setParentForTask(string $taskId, string $parentId, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskId/setParent", [
			'json' => [
				'parent' => $parentId
			],
			'query' => $options
		]);
	}

	/**
	 * Get stories for a task (comments, system activities)
	 *
	 * @param string $taskId The task ID
	 * @param array $options Optional parameters (opt_fields, limit, offset)
	 * @return array
	 */
	public function getTaskStories(string $taskId, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskId/stories", [
			'query' => $options
		]);
	}

	/**
	 * Add followers to a task
	 *
	 * @param string $taskId The task ID
	 * @param array $followerIds Array of user IDs to add as followers
	 * @param array $options Optional parameters (opt_fields)
	 * @return array
	 */
	public function addFollowers(string $taskId, array $followerIds, array $options = [])
	{
		return $this->client->request('POST', "tasks/$taskId/addFollowers", [
			'json' => [
				'followers' => $followerIds
			],
			'query' => $options
		]);
	}
}