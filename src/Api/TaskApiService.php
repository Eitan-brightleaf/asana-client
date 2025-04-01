<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Http\AsanaApiClient;
use GuzzleHttp\Exception\RequestException;

class TaskApiService
{
    /**
     * An HTTP client instance configured to interact with the Asana API.
     *
     * This property stores an instance of AsanaApiClient which handles all HTTP communication
     * with the Asana API endpoints. It provides authenticated access to API resources and
     * manages request/response handling.
     */
    private AsanaApiClient $client;

    /**
     * Constructor for initializing the service with an Asana API client.
     *
     * Sets up the service instance using the provided Asana API client.
     *
     * @param AsanaApiClient $client The Asana API client instance used to interact with the Asana API.
     *
     * @return void
     */
    public function __construct(AsanaApiClient $client)
	{
		$this->client = $client;
	}


    /**
     * Get multiple tasks
     *
     * Returns a list of tasks filtered by the specified criteria. This endpoint provides a way to get
     * multiple tasks in a single request according to your search parameters.
     *
     * API Documentation: https://developers.asana.com/docs/get-multiple-tasks
     *
     * @param array $options Query parameters to filter and format results:
     *                      Filtering parameters:
     *                      - assignee (string): Filter tasks by assignee. Can be 'me', a user ID, or null
     *                      - project (string): Filter tasks by project. Can be project ID or null
     *                      - section (string): Filter tasks by section. Can be section ID or null
     *                      - workspace (string): Filter tasks by workspace. Can be workspace ID or null
     *                      - completed_since (string): ISO 8601 timestamp or 'now' for recently completed tasks
     *                      - modified_since (string): ISO 8601 timestamp for tasks modified after a time
     *                      - limit (int): Maximum number of tasks to return. Default is 20
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty (bool): Returns prettier formatting in responses
     *
     * @return array List of tasks matching the filters. Each task contains:
     *               - gid: Task's unique identifier
     *               - name: Task name/title
     *               - resource_type: Always "task"
     *               Additional fields if specified in opt_fields
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid parameter values
     *                         - Insufficient permissions
     *                         - Rate limiting
     *                         - Network connectivity issues
     */
	public function getTasks(array $options): array {
		return $this->client->request('GET', 'tasks', ['query' => $options]);
	}

    /**
     * Create a task
     *
     * Creates a new task in an Asana workspace or project. The task can be assigned to a specific user,
     * given a due date and notes, and added to projects and tags.
     *
     * API Documentation: https://developers.asana.com/docs/create-a-task
     *
     * @param array $data Data for creating the task. Supported fields include (but are not limited to):
     *                    Required:
     *                    - workspace (string): GID of workspace to create task in. Only required if no project or parent task is specified.
     *                    Optional:
     *                    - name (string): Name/title of the task
     *                    - assignee (string|null): GID of user to assign to, or null
     *                    - completed (bool): Whether the task is completed
     *                    - due_on (string|null): Due date in YYYY-MM-DD format
     *                    - due_at (string|null): Due date with time in UTC format
     *                    - followers (array): Array of user GIDs to add as followers
     *                    - notes (string): Task description/notes
     *                    - parent (string|null): GID of parent task for subtasks
     *                    Example: ["name" => "New task", "workspace" => "12345"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Return formatted JSON
     *                      Example: ["opt_fields" => "name,assignee,completed"]
     *
     * @return array Task data including at minimum:
     *               - gid: Unique task identifier
     *               - resource_type: Always "task"
     *               - name: Task name/title
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If the API request fails due to:
     *                         - Missing required fields
     *                         - Invalid field values
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
	public function createTask(array $data, array $options = []): array {
		return $this->client->request('POST', 'tasks', ['json' => $data, 'query' => $options]);
	}

    /**
     * Get a task
     *
     * Returns the complete task record for a single task. The task record includes
     * basic metadata (name, notes, completion status, etc.) along with any custom
     * fields, followers, assignee and more (or less) as requested via opt_fields.
     *
     * API Documentation: https://developers.asana.com/docs/get-a-task
     *
     * @param string $taskGid The unique global ID of the task to retrieve. This identifier
     *                        can be found in the task URL or returned from task-related API endpoints.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                        Common fields include: name, notes, assignee, completed, due_on,
     *                        projects, tags, workspace
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      Example: ["opt_fields" => "name,assignee,completed"]
     *
     * @return array Task record containing at minimum:
     *               - gid: Unique task identifier
     *               - resource_type: Always "task"
     *               - name: Task name/title
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid task GID provided, insufficient permissions,
     *                         network issues, or rate limiting occurs
     */
	public function getTask(string $taskGid, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskGid", ['query' => $options]);
	}

    /**
     * Update a task
     *
     * Updates the properties of a task. Tasks can be updated to change things like their name,
     * assignee, completion state, due date, and other properties. Some of the properties that can be updated
     * are documented in the parameters section. For a complete list visit the official documentation. Any unspecified fields remain unchanged.
     *
     * API Documentation: https://developers.asana.com/docs/update-a-task
     *
     * @param string $taskGid The unique global ID of the task to update. This identifier can
     *                        be found in the task URL or returned from task-related API endpoints.
     *                        Example: "12345"
     * @param array $data The properties of the task to update. Can include:
     *                    - name (string): Name of the task
     *                    - assignee (string|null): GID of user to assign or null to unassign
     *                    - completed (bool): Whether the task is complete
     *                    - due_on (string|null): Due date in YYYY-MM-DD format
     *                    - due_at (string|null): Due date with time in UTC format
     *                    - notes (string): Task description/notes
     *                    Example: ["name" => "Update Task", "completed" => true]
     * @param array $options Optional parameters for the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Return formatted JSON
     *                      Example: ["opt_fields" => "name,assignee,completed"]
     *
     * @return array The updated task data including:
     *               - gid: Unique task identifier
     *               - resource_type: Always "task"
     *               - name: Updated task name
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid task GID provided, malformed data,
     *                         insufficient permissions, or network issues occur
     */
	public function updateTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('PUT', "tasks/$taskGid", ['json' => $data, 'query' => $options]);
	}

    /**
     * Delete a task
     *
     * Deletes a task and moves it to the trash of the user making the delete request.
     * Tasks can be recovered from the trash within 30 days. After 30 days, deleted tasks
     * are permanently removed from the system and cannot be recovered.
     *
     * API Documentation: https://developers.asana.com/docs/delete-a-task
     *
     * @param string $taskGid The unique global ID of the task to delete/trash.
     *                        This identifier can be found in the task URL
     *                        or returned from task-related API endpoints.
     *                        Example: "12345"
     *
     * @return array Empty data object containing only the HTTP status indicator:
     *               - data: An empty JSON object {}
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Insufficient permissions to delete/trash the task
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
	public function deleteTask(string $taskGid): array {
		return $this->client->request('DELETE', "tasks/$taskGid");
	}

    /**
     * Duplicate a task
     *
     * Creates and returns a job that will duplicate a task, copying its properties and memberships
     * to a new task. Fields like assignee, name, notes, projects, etc. can be overridden in the duplicated task.
     *
     * API Documentation: https://developers.asana.com/docs/duplicate-a-task
     *
     * @param string $taskGid The unique global ID of the task to duplicate.
     *                        This identifier can be found in the task URL or returned from task-related API endpoints.
     *                        Example: "12345"
     * @param array $data Data for the duplicated task. Can include:
     *                    - name: (string) Name of the new duplicated task
     *                    - include: (string) Comma-separated list of fields to duplicate: assignee,attachments,
     *                              dates,dependencies,followers,notes,parent,projects,subtasks,tags
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Return formatted JSON
     *
     * @return array Data about the duplication job in progress:
     *               - gid: Unique identifier of the job
     *               - resource_type: Always "job"
     *               - resource_subtype: Type of job
     *               - status: Current job status (e.g. "not_started", "in_progress", "succeeded")
     *               - new_task: Contains the duplicated task data once job is complete
     *
     * @throws RequestException For invalid task GIDs, malformed data,
     *                         insufficient permissions, or network issues
     */
	public function duplicateTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/duplicate", ['json' => $data, 'query' => $options]);
	}

    /**
     * Get tasks from a project
     *
     * Returns compact task records that are contained within the specified project. Tasks can exist
     * in multiple projects at once. By default, tasks included are not sorted and basic task fields
     * are returned. Tasks may be filtered by specifying the query options.
     *
     * API Documentation: https://developers.asana.com/docs/get-tasks-from-a-project
     *
     * @param string $projectGid The unique global ID of the project to get tasks from.
     *                          This identifier can be found in the project URL or
     *                          returned from project-related API endpoints.
     *                          Example: "12345"
     * @param array $options Optional query parameters to customize the request:
     *                      - completed_since (string): Only return tasks that are either incomplete or that have been completed since this time. Accepts a date-time string or the keyword "now".
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     *
     * @return array List of tasks in the project containing at minimum:
     *               - gid: Task identifier
     *               - name: Task name
     *               - resource_type: Always "task"
     *               Additional fields if specified in opt_fields
     *
     * @throws RequestException If invalid project GID provided, permission errors,
     *                         network issues, or rate limiting occurs
     */
	public function getTasksByProject(string $projectGid, array $options = []): array {
		return $this->client->request('GET', "projects/$projectGid/tasks", ['query' => $options]);
	}

    /**
     * Get tasks from a section
     *
     * Returns a list of tasks in a section. Tasks can be placed into a section within a project.
     * This endpoint allows retrieving all tasks that are currently in a specific section.
     *
     * API Documentation: https://developers.asana.com/docs/get-tasks-from-a-section
     *
     * @param string $sectionGid The unique global ID of the section to query tasks from.
     *                          Found in the URL or API responses for a section.
     *                          Example: "12345"
     * @param array $options Optional parameters for customizing the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     *
     * @return array List of tasks containing at minimum:
     *               - gid: Task identifier
     *               - name: Task name
     *               - resource_type: Always "task"
     *               Additional fields if specified in opt_fields
     *
     * @throws RequestException If invalid section GID provided, permission errors,
     *                         network issues, or rate limiting occurs
     */
	public function getTasksBySection(string $sectionGid, array $options = []): array {
		return $this->client->request('GET', "sections/$sectionGid/tasks", ['query' => $options]);
	}

    /**
     * Get tasks from a tag
     *
     * Returns a list of all tasks with the specified tag. Tasks can have multiple tags
     * and this endpoint allows retrieving all tasks associated with a particular tag.
     *
     * API Documentation: https://developers.asana.com/docs/get-tasks-from-a-tag
     *
     * @param string $tagGid The global identifier for the tag to query tasks from.
     *                       Found in the URL or API responses for a tag.
     *                       Example: "12345"
     * @param array $options Optional parameters for customizing the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     *
     * @return array List of tasks containing at minimum:
     *               - gid: Task identifier
     *               - name: Task name
     *               - resource_type: Always "task"
     *               Additional fields if specified in opt_fields
     *
     * @throws RequestException If invalid tag GID is provided, permission errors,
     *                         network issues, or rate limiting occurs
     */
	public function getTasksByTag(string $tagGid, array $options = []): array {
		return $this->client->request('GET', "tags/$tagGid/tasks", ['query' => $options]);
	}

    /**
     * Get tasks from a user task list
     *
     * Retrieves the compact list of tasks in a user's My Tasks list. The My Tasks list
     * represents the tasks assigned to a user that also appear in their My Tasks list.
     * Users can reorder their My Tasks list and specify custom sections to group tasks.
     *
     * API Documentation: https://developers.asana.com/docs/get-tasks-from-a-user-task-list
     *
     * @param string $userTaskListGid The globally unique identifier for the user task list.
     *                                This can be found in the URL of a user's My Tasks list
     *                                or via the user_task_list endpoints.
     *                                Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - limit (int): Number of items to return per page (1-100)
     *                      - offset (string): Offset token for pagination
     *
     * @return array A list of tasks in the user's My Tasks list. Each task contains:
     *               - gid: Unique identifier of the task
     *               - name: Name/title of the task
     *               - resource_type: Always "task"
     *               Additional fields as specified via opt_fields
     *
     * @throws RequestException When the API request fails due to:
     *                         - Invalid user task list GID
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
	public function getTasksByUserTaskList(string $userTaskListGid, array $options = []): array {
		return $this->client->request('GET', "user_task_lists/$userTaskListGid/tasks", ['query' => $options]);
	}

    /**
     * Get subtasks from a task
     *
     * Retrieves a compact list of all subtasks associated with the given task. A subtask is a task that
     * represents a breakdown of a larger task and maintains a parent-child relationship with its parent task.
     *
     * API Documentation: https://developers.asana.com/docs/get-subtasks-from-a-task
     *
     * @param string $taskGid The unique global ID of the parent task for which to retrieve subtasks.
     *                        This identifier can be found in the task URL or returned from
     *                        task-related API endpoints.
     * @param array $options Optional query parameters to customize the request. Supported parameters include:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Whether to return prettified JSON
     *                      - limit: The number of objects to return per page. Default: 20, Maximum: 100
     *                      - offset: Used for pagination, marks the beginning of page
     *
     * @return array A list of subtasks belonging to the specified parent task. Each subtask entry contains:
     *               - gid: Unique identifier of the subtask
     *               - name: Name of the subtask
     *               - resource_type: Will be "task"
     *               Additional fields as specified via opt_fields parameter
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Insufficient permissions to access the task
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
	public function getSubtasksFromTask(string $taskGid, array $options = []): array {
		return $this->client->request('GET', "tasks/$taskGid/subtasks", ['query' => $options]);
	}

    /**
     * Create a subtask
     *
     * Creates and returns a subtask of an existing parent task. Creating a new subtask requires
     * providing the parent task's GID and basic details for the subtask like its name. The subtask
     * will be added to any projects the parent task is in.
     *
     * API Documentation: https://developers.asana.com/docs/create-a-subtask
     *
     * @param string $taskGid The unique global ID of the parent task under which to create the subtask.
     *                        This identifier can be found in the task URL or via API responses.
     *                        Example: "12345"
     * @param array $data The data for creating the subtask.
     *                    Optional fields include but are not limited to:
     *                     - name: The name/title of the subtask
     *                    - assignee: GID of user to assign to
     *                    - notes: Additional notes/description
     *                    - due_on: Due date in YYYY-MM-DD format
     *                    - completed: Boolean for completion status
     *                    Example: ["name" => "My Subtask"]
     * @param array $options Optional parameters to include with the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Return formatted JSON
     *
     * @return array The created subtask data including:
     *               - gid: Unique identifier of created subtask
     *               - resource_type: Always "task"
     *               - name: Name of the subtask
     *               - Additional fields as specified in opt_fields
     *
     * @throws RequestException For invalid parent task GIDs, malformed data,
     *                         insufficient permissions, or network issues
     */
	public function createSubtaskForTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/subtasks", ['json' => $data, 'query' => $options]);
	}

    /**
     * Set the parent of a task
     *
     * Changes the parent of a task by specifying a new parent task. Tasks can
     * only have one parent at a time, and a task cannot be made its own parent. Setting parent to null makes
     * the task a top-level task.
     *
     * API Documentation: https://developers.asana.com/docs/set-the-parent-of-a-task
     *
     * @param string $taskGid Global ID of the task whose parent will be changed. Can be found in the
     *                        task URL or via API responses. Example: "12345"
     * @param array $data Data payload specifying the parent task.
     *                    Required:
     *                    - parent (string|null): GID of the new parent task, or null to make top-level.
     *                    Optional:
     *                    - insert_before (string|null): GID of task to insert before, null for top
     *                    - insert_after (string|null): GID of task to insert after, null for bottom
     *                    Example: ["parent" => "67890"] or
     *                            ["parent" => "67890", "insert_before" => "12345"] or
     *                            ["parent" => null, "insert_after" => "12345"]
     * @param array $options Additional request parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Returns formatted JSON if true
     *
     * @return array Task data including:
     *               - gid: Task identifier
     *               - resource_type: Always "task"
     *               - name: Task name
     *               - parent: Parent task info if set
     *
     * @throws RequestException For invalid task IDs, permission errors, network issues,
     *                         or if attempting to create circular dependencies
     */
	public function setParentForTask(string $taskGid, array $data, array $options = []): array {
		return $this->client->request('POST', "tasks/$taskGid/setParent", ['json' => $data, 'query' => $options]);
	}

    /**
     * Get dependencies from a task
     *
     * Returns a compact list of tasks that this task depends on. A task's dependencies are those tasks that need to be
     * completed before the task itself can be completed. For example, if task A is a dependency of task B, then task B
     * cannot be marked complete until task A is first completed.
     *
     * API Documentation: https://developers.asana.com/docs/get-dependencies-from-a-task
     *
     * @param string $taskGid The unique global ID of the task from which to fetch dependencies.
     *                        This identifier can be found in the task URL or returned from
     *                        task-related API endpoints.
     * @param array $options Optional query parameters to customize the request. Supported parameters include:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Whether to return prettified JSON
     *                      - limit: The number of objects to return per page. Default: 20, Maximum: 100
     *                      - offset: Used for pagination, marks the beginning of page
     *
     * @return array A list of tasks that the specified task depends on. Each task entry contains:
     *               - gid: Unique identifier of the dependency task
     *               - name: Name of the dependency task
     *               - resource_type: Will be "task"
     *               Additional fields can be requested via opt_fields parameter
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Insufficient permissions to access the task
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
    public function getDependenciesFromTask(string $taskGid, array $options = []): array
    {
		return $this->client->request('GET', "tasks/$taskGid/dependencies", ['query' => $options]);
	}

    /**
     * Set dependencies for a task
     *
     * Marks other tasks as dependencies of this task. Dependencies must be completed before the task
     * can be completed. A task can have multiple dependencies and dependencies can be chained
     * (i.e., a dependency can have its own dependencies). There is a limit of 30 total dependencies
     * and dependents combined per task.
     *
     * API Documentation: https://developers.asana.com/docs/set-dependencies-for-a-task
     *
     * @param string $taskGid The unique global ID of the task to set dependencies for.
     *                        This identifier can be found in the task URL or returned from
     *                        task-related API endpoints.
     * @param array $data An array containing dependencies to add. Must include:
     *                    - dependencies (array): Array of task GIDs to set as dependencies.
     *                      Each GID must be a string representing a valid task.
     *                      Example: ['1234', '5678']
     *                    Note: There is a limit of 30 total dependencies and dependents combined per task.
     *
     * @return array The updated task data with the list of current dependencies
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Invalid dependency task GIDs
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     *                         - Circular dependencies
     *                         - Exceeding the 30 total dependencies/dependents limit
     */
    public function setDependenciesForTask(string $taskGid, array $data): array
    {
		return $this->client->request('POST', "tasks/$taskGid/addDependencies", ['json' => $data]);
	}

    /**
     * Unlink dependencies from a task
     *
     * Removes the specified dependencies from a task. Dependencies are the tasks that need to be
     * completed before the current task can begin. For example, if task A is a dependency of task B,
     * then task A must be finished before task B can be started. A task can't be dependent on itself
     * or create a circular dependency chain.
     *
     * API Documentation: https://developers.asana.com/docs/unlink-dependencies-from-a-task
     *
     * @param string $taskGid The unique global ID of the task from which to remove dependencies.
     *                        This identifier can be found in the task URL or returned from
     *                        task-related API endpoints.
     * @param array $data An array containing dependencies to remove. Must include:
     *                    - dependencies (array): Array of task GIDs to remove as dependencies.
     *                      Each GID must be a string representing a valid task.
     *                      Example: ['1234', '5678']
     *
     * @return array The updated task data with the list of current dependencies after removal
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Invalid dependency task GIDs
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     */
    public function unlinkDependenciesFromTask(string $taskGid, array $data): array
    {
		return $this->client->request('POST', "tasks/$taskGid/removeDependencies", ['json' => $data]);
	}

    /**
     * Get dependents from a task
     *
     * Returns a compact list of tasks that are dependents of this task. A task's dependents
     * are those tasks that depend on this task's completion. For instance, if task B depends on task A,
     * task B cannot be started until task A is completed. In this case, task A would return task B
     * as its dependent.
     *
     * API Documentation: https://developers.asana.com/docs/get-dependents-from-a-task
     *
     * @param string $taskGid The unique global ID of the task from which to fetch dependents.
     *                        This identifier can be found in the task URL or returned from
     *                        task-related API endpoints.
     * @param array $options Optional query parameters to customize the request. Supported parameters include:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
     *                      - opt_pretty: Whether to return prettified JSON
     *                      - limit: The number of objects to return per page. Default: 20, Maximum: 100
     *                      - offset: Used for pagination, marks the beginning of page
     *
     * @return array A list of tasks that depend on the specified task. Each task entry contains:
     *               - gid: Unique identifier of the dependent task
     *               - name: Name of the dependent task
     *               - resource_type: Will be "task"
     *               Additional fields can be requested via opt_fields parameter
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Insufficient permissions to access the task
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
    public function getDependentsFromTask(string $taskGid, array $options = []): array
    {
		return $this->client->request('GET', "tasks/$taskGid/dependents", ['query' => $options]);
	}

    /**
     * Set dependents for a task
     *
     * Adds tasks that depend on the completion of this task. These dependent tasks cannot start
     * until this task is completed. Once this task is marked complete, its dependent tasks will be
     * allowed to start. A task can have multiple dependent tasks, and dependencies can be chained
     * to create sequential workflows. Note that there is a limit of 30 total dependencies and
     * dependents combined per task.
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
     *                    Note: There is a limit of 30 total dependencies and dependents combined per task.
     *
     * @return array The updated task data with the list of current dependent tasks
     * @throws RequestException If the API request fails due to:
     *                         - Invalid task GID
     *                         - Invalid dependent task GIDs
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     *                         - Circular dependencies
     *                         - Exceeding the 30 total dependencies/dependents limit
     */
	public function setDependentsForTask(string $taskGid, array $data): array {
		return $this->client->request('POST', "tasks/$taskGid/addDependents", ['json' => $data]);
	}

    /**
     * Unlink dependents from a task
     *
     * Removes the specified dependent tasks from a task. This endpoint removes the link between
     * the tasks but does not delete the tasks themselves. Dependent tasks are those that cannot
     * start until the current task is completed. If task B depends on task A, then task A must
     * be completed before task B can begin.
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
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
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
     *                      - opt_fields (string): A comma-separated list of fields to include in the response (e.g., "name,assignee.status,custom_fields.name")
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
