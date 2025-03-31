<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Http\AsanaApiClient;
use GuzzleHttp\Exception\RequestException;

class TaskApiService
{
	private AsanaApiClient $client;

	public function __construct(AsanaApiClient $client)
	{
		$this->client = $client;
	}


	/**
	 * Get multiple tasks
	 * https://developers.asana.com/docs/get-multiple-tasks
	 */
	public function getTasks(array $options): array {
		return $this->client->request('GET', 'tasks', ['query' => $options]);
	}

	/**
	 * Create a task
	 * https://developers.asana.com/docs/create-a-task
	 */
	public function createTask(array $data, array $options = []): array {
		return $this->client->request('POST', 'tasks', ['json' => $data, 'query' => $options]);
	}

	/**
	 * Get a task
	 * https://developers.asana.com/docs/get-a-task
	 */
	public function getTask(string $taskGid, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskGid", ['query' => $options]);
	}

	/**
	 * Update a task
	 * https://developers.asana.com/docs/update-a-task
	 */
	public function updateTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('PUT', "tasks/$taskGid", ['json' => $data, 'query' => $options]);
	}

	/**
	 * Delete a task
	 * https://developers.asana.com/docs/delete-a-task
	 */
	public function deleteTask(string $taskGid): array {
		return $this->client->request('DELETE', "tasks/$taskGid");
	}

	/**
	 * Duplicate a task
	 * https://developers.asana.com/docs/duplicate-a-task
	 */
	public function duplicateTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/duplicate", ['json' => $data, 'query' => $options]);
	}

	/**
	 * Get tasks from a project
	 * https://developers.asana.com/docs/get-tasks-from-a-project
	 */
	public function getTasksByProject(string $projectGid, array $options = []): array {
		return $this->client->request('GET', "projects/$projectGid/tasks", ['query' => $options]);
	}

	/**
	 * Get tasks from a section
	 * https://developers.asana.com/docs/get-tasks-from-a-section
	 */
	public function getTasksBySection(string $sectionGid, array $options = []): array {
		return $this->client->request('GET', "sections/$sectionGid/tasks", ['query' => $options]);
	}

	/**
	 * Get tasks from a tag
	 * https://developers.asana.com/docs/get-tasks-from-a-tag
	 */
	public function getTasksByTag(string $tagGid, array $options = []): array {
		return $this->client->request('GET', "tags/$tagGid/tasks", ['query' => $options]);
	}

	/**
	 * Get tasks from a user task list
	 * https://developers.asana.com/docs/get-tasks-from-a-user-task-list
	 */
	public function getTasksByUserTaskList(string $userTaskListGid, array $options = []): array {
		return $this->client->request('GET', "user_task_lists/$userTaskListGid/tasks", ['query' => $options]);
	}

	/**
	 * Get subtasks from a task
	 * https://developers.asana.com/docs/get-subtasks-from-a-task
	 */
	public function getSubtasksFromTask(string $taskGid, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskGid/subtasks", ['query' => $options]);
	}

	/**
	 * Create a subtask
	 * https://developers.asana.com/docs/create-a-subtask
	 */
	public function createSubtaskForTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/subtasks", ['json' => $data, 'query' => $options]);
	}

	/**
	 * Set the parent of a task
	 * https://developers.asana.com/docs/set-the-parent-of-a-task
	 */
	public function setParentForTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/setParent", ['json' => $data, 'query' => $options]);
	}

	/**
	 * Get dependencies from a task
	 * https://developers.asana.com/docs/get-dependencies-from-a-task
	 */
	public function getDependenciesFromTask(string $taskGid, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskGid/dependencies", ['query' => $options]);
	}

	/**
	 * Set dependencies for a task
	 * https://developers.asana.com/docs/set-dependencies-for-a-task
	 */
	public function setDependenciesForTask(string $taskGid, array $data): array {
		return $this->client->request('POST', "tasks/$taskGid/addDependencies", ['json' => $data]);
	}

	/**
	 * Unlink dependencies from a task
	 * https://developers.asana.com/docs/unlink-dependencies-from-a-task
	 */
	public function unlinkDependenciesFromTask(string $taskGid, array $data): array {
		return $this->client->request('POST', "tasks/$taskGid/removeDependencies", ['json' => $data]);
	}

	/**
	 * Get dependents from a task
	 * https://developers.asana.com/docs/get-dependents-from-a-task
	 */
	public function getDependentsFromTask(string $taskGid, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskGid/dependents", ['query' => $options]);
	}

	/**
	 * Set dependents for a task
	 *
	 * Marks the specified tasks as dependents of a task. A task's dependents have their schedule
	 * determined by the dependencies task, giving the dependent tasks a start date based on the date
	 * their dependencies are completed. todo is this real description????
	 *
	 * API Documentation: https://developers.asana.com/docs/set-dependents-for-a-task
	 *
	 * @param string $taskGid The unique global ID of the task for which to set dependents.
	 *                        This identifier can be found in the task URL or returned from
	 *                        task-related API endpoints.
	 * @param array $data An array containing dependent tasks to add. Must include:
	 *                    - dependents (array): Array of task GIDs to set as dependents.
	 *                      Each GID must be a string representing a valid task.
	 *                      Example: ['1234', '5678']
	 *
	 * @return array The updated task data with the list of current dependent tasks
	 * @throws RequestException If the API request fails due to:
	 *                         - Invalid task GID
	 *                         - Invalid dependent task GIDs
	 *                         - Insufficient permissions
	 *                         - Network connectivity issues
	 *                         - Circular dependencies
	 */
	public function setDependentsForTask(string $taskGid, array $data): array {
		return $this->client->request('POST', "tasks/$taskGid/addDependents", ['json' => $data]);
	}

	/**
	 * Unlink dependents from a task
	 *
	 * Removes the specified dependent tasks from a task. This endpoint removes the link between
	 * the tasks but does not delete the tasks themselves. Dependent tasks have their schedule
	 * determined by their parent tasks.
	 *
	 * API Documentation: https://developers.asana.com/docs/unlink-dependents-from-a-task
	 *
	 * @param string $taskGid The unique global ID of the task from which to remove dependents.
	 *                        This identifier can be found in the task URL or returned from
	 *                        task-related API endpoints.
	 * @param array $data An array containing dependent tasks to remove. Must include:
	 *                    - dependents (array): Array of task GIDs to remove as dependents.
	 *                      Each GID must be a string representing a valid task.
	 *                      Example: ['1234', '5678']
	 *
	 * @return array The updated task data with current list of dependent tasks after removal
	 * @throws RequestException If the API request fails due to:
	 *                         - Invalid task GID
	 *                         - Invalid dependent task GIDs
	 *                         - Insufficient permissions
	 *                         - Network connectivity issues
	 */
	public function unlinkDependentsFromTask(string $taskGid, array $data): array {
		return $this->client->request('POST', "tasks/$taskGid/removeDependents", ['json' => $data]);
	}

	/**
	 * Add a project to a task
	 *
	 * Associates a task with a project. Tasks can be members of multiple projects at once, and
	 * adding a task to a project will automatically add its parent project to the task.
	 *
	 * API Documentation: https://developers.asana.com/docs/add-a-project-to-a-task
	 *
	 * @param string $taskGid The unique global ID of the task that will be added to the project.
	 *                        This identifier can be found in the task URL or returned from
	 *                        task-related API endpoints.
	 * @param string $projectGid The unique global ID of the project that the task will be added to.
	 *                          This identifier can be found in the project URL or returned from
	 *                          project-related API endpoints.
	 * @param array $data Optional data array containing additional parameters:
	 *                    - insert_before (string): A task gid within the project to insert the task before or null to insert at the beginning of the list
	 *                    - insert_after (string): A task gid within the project to insert the task after or null to insert at the end of the list
	 *                    - section (string): A section gid in the project to add the task to
	 *
	 * @return array The updated task data showing current project associations
	 * @throws RequestException If the API request fails due to invalid task GID, invalid project GID,
	 *                         insufficient permissions, or network issues
	 */
	public function addProjectToTask(string $taskGid, string $projectGid, array $data = []): array {
		$data['project'] = $projectGid;
		return $this->client->request('POST', "tasks/$taskGid/addProject", ['json' => $data]);
	}

	/**
	 * Remove a project from a task
	 *
	 * Removes the specified project from a task. The task will no longer be associated with
	 * the project, but will remain accessible in other projects and in the user's task list.
	 *
	 * API Documentation: https://developers.asana.com/docs/remove-a-project-from-a-task
	 *
	 * @param string $taskGid The unique global ID of the task from which to remove the project. This identifier
	 *                        can be found in the task URL or returned from task-related API endpoints.
	 * @param string $projectGid The unique global ID of the project to remove from the task. This identifier
	 *                          can be found in the project URL or returned from project-related API endpoints.
	 *
	 * @return array The updated task data showing current project associations after removal
	 * @throws RequestException If the API request fails due to invalid task GID, invalid project GID,
	 *                         insufficient permissions, or network issues
	 */
	public function removeProjectFromTask(string $taskGid, string $projectGid): array {
		return $this->client->request('POST', "tasks/$taskGid/removeProject", ['json' => ['project' => $projectGid]]);
	}

	/**
	 * Add a tag to a task
	 *
	 * Associates a tag with a task. Tags provide a way to organize tasks and make them more searchable.
	 * A task can have multiple tags, and adding a tag that is already on the task will not create a duplicate.
	 *
	 * API Documentation: https://developers.asana.com/docs/add-a-tag-to-a-task
	 *
	 * @param string $taskGid The unique global ID of the task to which the tag will be added.
	 *                        This identifier can be found in the task URL or returned from
	 *                        task-related API endpoints.
	 * @param string $tagGid The unique global ID of the tag to add to the task.
	 *                       This identifier can be found in the tag URL or returned from
	 *                       tag-related API endpoints.
	 *
	 * @return array The updated task data with the current tags after addition
	 * @throws RequestException If the API request fails due to invalid task GID, invalid tag GID,
	 *                         insufficient permissions, or network issues
	 */
	public function addTagToTask(string $taskGid, string $tagGid): array {
		return $this->client->request('POST', "tasks/$taskGid/addTag", ['json' => ['tag' => $tagGid]]);
	}

	/**
	 * Remove a tag from a task
	 *
	 * Removes a tag from a task. The task will no longer be associated with the specified tag.
	 * Tags provide a way to organize tasks and make them more searchable.
	 *
	 * API Documentation: https://developers.asana.com/docs/remove-a-tag-from-a-task
	 *
	 * @param string $taskGid The unique global ID of the task from which to remove the tag.
	 *                        This identifier can be found in the task URL or returned from
	 *                        task-related API endpoints.
	 * @param string $tagGid The unique global ID of the tag to remove from the task.
	 *                       This identifier can be found in the tag URL or returned from
	 *                       tag-related API endpoints.
	 *
	 * @return array The updated task data with the current tags after removal
	 * @throws RequestException If the API request fails due to invalid task GID, invalid tag GID,
	 *                         insufficient permissions, or network issues
	 */
	public function removeTagFromTask(string $taskGid, string $tagGid): array {
		return $this->client->request('POST', "tasks/$taskGid/removeTag", ['json' => ['tag' => $tagGid]]);
	}

	/**
	 * Add followers to a task
	 *
	 * Adds one or more followers to a task. A follower in Asana is a user that will receive notifications
	 * about any changes or comments made to the task.
	 *
	 * API Documentation: https://developers.asana.com/docs/add-followers-to-a-task
	 *
	 * @param string $taskGid The unique global ID of the task to which followers will be added. This identifier
	 *                        can be found in the task URL or returned from task-related API endpoints.
	 * @param array $followers An array of user GIDs representing the followers to add to the task.
	 *                        Each GID should be a string that uniquely identifies a user in Asana.
	 *                        Example: ['12345', '67890']
	 * @param array $options Optional query parameters to customize the request. Supported parameters include:
	 *                      - opt_fields: Comma-separated list of fields to include in the response
	 *                      - opt_pretty: Whether to return prettified JSON
	 *
	 * @return array The updated task data including information about the new followers
	 * @throws RequestException If the API request fails due to invalid task GID, invalid user GIDs,
	 *                         insufficient permissions, or network issues
	 */
	public function addFollowersToTask(string $taskGid, array $followers, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/addFollowers", ['json' => ['followers' => $followers], 'query' => $options]);
	}

	/**
	 * Remove followers from a task.
	 *
	 * Removes one or more followers from a task. A follower in Asana is a user that will receive notifications
	 * about any changes or comments made to the task.
	 *
	 * API Documentation: https://developers.asana.com/docs/remove-followers-from-a-task
	 *
	 * @param string $taskGid The unique global ID of the task from which to remove followers. This identifier
	 *                        can be found in the task URL or returned from task-related API endpoints.
	 * @param array $followers An array of user GIDs representing the followers to remove from the task.
	 *                        Each GID should be a string that uniquely identifies a user in Asana.
	 *                        Example: ['12345', '67890']
	 * @param array $options Optional query parameters to customize the request. Supported parameters include:
	 *                      - opt_fields: Comma-separated list of fields to include in the response
	 *                      - opt_pretty: Whether to return prettified JSON
	 *
	 * @return array The updated task data including information about the remaining followers
	 * @throws RequestException If the API request fails due to invalid task GID, invalid user GIDs,
	 *                         insufficient permissions, or network issues
	 */
	public function removeFollowersFromTask(string $taskGid, array $followers, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/removeFollowers", ['json' => ['followers' => $followers], 'query' => $options]);
	}

	/**
	 * Get a task by a given custom ID.
	 *
	 * Fetches a task from a specific workspace using its custom task ID.
	 * The `custom_task_id` must be unique within the workspace. If no task matches
	 * the provided custom ID, an error will be returned.
	 *
	 * API Documentation: https://developers.asana.com/reference/gettaskforcustomid
	 *
	 * @param string $workspaceGid The unique global ID of the workspace where the task is searched.
	 * @param string $customId The custom task ID to retrieve.
	 *
	 * @return array An associative array representing the task.
	 *
	 * @throws RequestException If the API request fails or no task with the provided custom ID is found.
	 */
	public function getTaskByCustomId(string $workspaceGid, string $customId): array {
		return $this->client->request('GET', "workspaces/$workspaceGid/tasks/custom_id/$customId");
	}

	/**
	 * Search tasks in a workspace.
	 *
	 * Executes a search query to retrieve tasks from a specific workspace using
	 * the Asana API. This method allows filtering tasks based on a variety of
	 * search options, such as assignee, completion status, and due dates.
	 * For details about available filters, refer to the Asana API documentation.
	 *
	 * API Documentation: https://developers.asana.com/reference/searchtasksforworkspace
	 *
	 * @param string $workspaceGid The unique global ID of the workspace where the tasks should be searched.
	 * @param array $options Optional query parameters to refine the search. Supported keys include:
	 *   - `text` (string): A full-text search string (e.g., portions of the task name or description).
	 *   - `resource_subtype` (string): Filter by task type. Common values are `default_task`, `milestone`, `section`.
	 *   - `completed` (bool): Filter by task completion status (`true` for completed, `false` for incomplete tasks).
	 *   - `completed_on.after` (string, ISO 8601): Include tasks completed after the given timestamp.
	 *   - `completed_on.before` (string, ISO 8601): Include tasks completed before the given timestamp.
	 *   - `created_on.after` (string, ISO 8601): Include tasks created after the given timestamp.
	 *   - `created_on.before` (string, ISO 8601): Include tasks created before the given timestamp.
	 *   - `modified_on.after` (string, ISO 8601): Include tasks modified after the given timestamp.
	 *   - `modified_on.before` (string, ISO 8601): Include tasks modified before the given timestamp.
	 *   - `due_on.after` (string, ISO 8601): Include tasks with a due date after the given timestamp.
	 *   - `due_on.before` (string, ISO 8601): Include tasks with a due date before the given timestamp.
	 *   - `due_on` (string, ISO 8601): Include tasks with a specific due date.
	 *   - `assignee.any` (array): A list of user GIDs; retrieves tasks assigned to any of the specified users.
	 *   - `assignee.not` (array): A list of user GIDs; excludes tasks assigned to any of the specified users.
	 *   - `projects.any` (array): A list of project GIDs; retrieves tasks in any of the specified projects.
	 *   - `projects.not` (array): A list of project GIDs; excludes tasks in any of the specified projects.
	 *   - `tags.any` (array): A list of tag GIDs; retrieves tasks tagged with any of the specified tags.
	 *   - `tags.not` (array): A list of tag GIDs; excludes tasks tagged with any of the specified tags.
	 *   - `opt_fields` (string): A comma-separated list of fields to include in the results.
	 *
	 * Example Usage:
	 * ```
	 * $workspaceGid = '123456789'; // Replace with your workspace GID
	 * $options = [
	 *     'completed' => false, // Retrieve only incomplete tasks
	 *     'assignee.any' => ['7891011'], // Filter tasks assigned to specific users
	 *     'due_on.before' => '2023-12-31T23:59:59Z', // Tasks due before the end of 2023
	 *     'opt_fields' => 'name,due_on,assignee.name', // Include additional task details in the result
	 * ];
	 *
	 * $tasks = $apiService->searchTasks($workspaceGid, $options);
	 *
	 * foreach ($tasks as $task) {
	 *     echo $task['name'] . " is due on " . $task['due_on'] . "\n";
	 * }
	 * ```
	 *
	 * @return array An array of tasks matching the search criteria. Each task entry
	 * includes fields specified in `opt_fields` or the default set by the Asana API.
	 *
	 * @throws RequestException If the API request fails due to connectivity issues or invalid query parameters.
	 */
	public function searchTasks(string $workspaceGid, array $options = []): array {
		return $this->client->request('GET', "workspaces/$workspaceGid/tasks/search", ['query' => $options]);
	}

	/**
	 * Mark a task as complete.
	 *
	 * Updates the status of a task to mark it as completed.
	 *
	 * @param string $taskGid The unique global ID of the task to be marked as complete.
	 *
	 * @return array The updated task details returned from the Asana API.
	 * @throws RequestException If the API request fails.
	 */
	public function markTaskComplete(string $taskGid): array {
		return $this->updateTask($taskGid, ['completed' => true]);
	}

	/**
	 * Reassign a task to a different user.
	 *
	 * Changes the assignee of a task to a specified user.
	 *
	 * @param string $taskGid The unique global ID of the task to be reassigned.
	 * @param string $assigneeGid The unique global ID of the user to whom the task should be reassigned.
	 *
	 * @return array The updated task details returned from the Asana API.
	 * @throws RequestException If the API request fails.
	 */
	public function reassignTask(string $taskGid, string $assigneeGid): array {
		return $this->updateTask($taskGid, ['assignee' => $assigneeGid]);
	}

	/**
	 * Get overdue tasks in a workspace.
	 *
	 * Retrieves tasks that are past their due date (`due_on.before`) and are not completed.
	 * This is useful for identifying tasks that have missed their deadlines.
	 *
	 * API Documentation: https://developers.asana.com/reference/searchtasksforworkspace
	 *
	 * @param string $workspaceGid The unique global ID of the workspace to search in.
	 * @param array|null $assigneeGids Optionally filter tasks by a specific assignee's GID.
	 * @param array $options Additional query parameters to refine the search. Supported keys include:
	 *   - `projects.any` (array): Filter tasks that belong to specific project(s) (optional).
	 *   - `tags.any` (array): Filter tasks that have specific tag(s) (optional).
	 *   - `opt_fields` (string): A comma-separated list of fields to include in the result (e.g., `name,due_on`).
	 *
	 * @return array A list of overdue tasks matching the specified criteria. Each task entry includes fields specified in `opt_fields` or defaults to Asana's standard fields.
	 *
	 * @throws RequestException If the API request fails due to connectivity issues or invalid query parameters.
	 */
	public function getOverdueTasks(string $workspaceGid, ?array $assigneeGids = null, array $options = []): array {
		$options['due_on.before'] = date('c'); // Include tasks with a due date before now (ISO 8601 format)
		$options['completed'] = false; // Exclude completed tasks

		// If an assignee is provided, filter tasks only for that user
		if ($assigneeGids) {
			$options['assignee.any'] = $assigneeGids; // Asana supports `assignee.any` for multiple users
		}

		// Ensure any other search filters are properly merged into the options array
		return $this->searchTasks($workspaceGid, $options);
	}
}
