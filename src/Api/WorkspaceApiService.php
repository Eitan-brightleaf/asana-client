<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

class WorkspaceApiService
{
    /**
     * The Asana API client instance
     *
     * Handles HTTP requests to the Asana API endpoints with proper authentication
     * and request formatting. This client manages the API connection details and
     * provides methods for making authenticated requests.
     *
     * @var AsanaApiClient An authenticated client for making Asana API requests
     */
    private AsanaApiClient $client;

    /**
     * Constructor
     *
     * Initializes the instance with the provided Asana API client. The client is
     * used to make authenticated requests to the Asana API.
     *
     * @param AsanaApiClient $client An instance of the AsanaApiClient responsible for
     *                               handling API requests and authentication.
     *
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get multiple workspaces
     *
     * Returns the compact representation of all workspaces visible to the authorized user.
     *
     * API Documentation: https://developers.asana.com/reference/getworkspaces
     *
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,email_domains,is_organization")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               List of workspaces containing at minimum:
     *               - gid: Workspace identifier
     *               - name: Workspace name
     *               - resource_type: Always "workspace"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing workspace list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If permission errors, network issues, or rate limiting occurs
     */
    public function getWorkspaces(array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', 'workspaces', ['query' => $options], $fullResponse);
    }

    /**
     * Get a workspace
     *
     * Returns the full workspace record for a single workspace.
     *
     * API Documentation: https://developers.asana.com/reference/getworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to retrieve.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,email_domains,is_organization")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               Workspace data containing at minimum:
     *               - gid: Workspace identifier
     *               - name: Workspace name
     *               - resource_type: Always "workspace"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing workspace data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getWorkspace(string $workspaceGid, array $options = [], bool $fullResponse = false): array
    {
        return $this->client->request('GET', "workspaces/$workspaceGid", ['query' => $options], $fullResponse);
    }

    /**
     * Update a workspace
     *
     * Updates the workspace with the provided data. Currently, only name can be updated.
     *
     * API Documentation: https://developers.asana.com/reference/updateworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to update.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $data The properties of the workspace to update. Can include:
     *                    - name (string): The name of the workspace
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,email_domains,is_organization")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               The updated workspace data including:
     *               - gid: Unique identifier
     *               - resource_type: Always "workspace"
     *               - name: Updated workspace name
     *               Additional fields as specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing workspace data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateWorkspace(
        string $workspaceGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'PUT',
            "workspaces/$workspaceGid",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Add a user to a workspace or organization
     *
     * Adds a user to a workspace or organization. The user can be referenced by their globally unique user ID or
     * their email address. Returns the full user record for the invited user.
     *
     * API Documentation: https://developers.asana.com/reference/adduserforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to add the user to.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $data Data for adding the user. Must include at least one of:
     *                    - user (string): GID of the user to add to the workspace
     *                    - email (string): Email address of the user to add to the workspace
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,email,photo")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               The full user record of the added user containing:
     *               - gid: User identifier
     *               - name: Username
     *               - email: User email
     *               - resource_type: Always "user"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing user data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, invalid user data,
     *                          insufficient permissions, or network issues occur
     */
    public function addUserToWorkspace(
        string $workspaceGid,
        array $data,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "workspaces/$workspaceGid/addUser",
            ['json' => ['data' => $data], 'query' => $options],
            $fullResponse
        );
    }

    /**
     * Remove a user from a workspace or organization
     *
     * Removes a user from a workspace or organization. The user making this call must be an admin
     * in the workspace. The user can be referenced by their globally unique user ID or their email address.
     * Returns an empty data block.
     *
     * API Documentation: https://developers.asana.com/reference/removeuserforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to remove the user from.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $data Data for removing the user. Must include at least one of:
     *                    - user (string): GID of the user to remove from the workspace
     *                    - email (string): Email address of the user to remove from the workspace
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               Empty data object containing only the HTTP status indicator:
     *               - data: An empty JSON object {}
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body (empty object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, invalid user data,
     *                          insufficient permissions, or network issues occur
     */
    public function removeUserFromWorkspace(
        string $workspaceGid,
        array $data,
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'POST',
            "workspaces/$workspaceGid/removeUser",
            ['json' => ['data' => $data]],
            $fullResponse
        );
    }

    /**
     * Get users in a workspace or organization
     *
     * Returns the user records for all users in the specified workspace or organization.
     *
     * API Documentation: https://developers.asana.com/reference/getusersforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get users from.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,email,photo")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               A list of user records containing:
     *               - gid: User identifier
     *               - name: Username
     *               - email: User email
     *               - resource_type: Always "user"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing user list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getUsersInWorkspace(
        string $workspaceGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/users",
            ['query' => $options],
            $fullResponse
        );
    }

    /**
     * Get teams in a workspace
     *
     * Returns the compact records for all teams in the workspace visible to the authorized user.
     *
     * API Documentation: https://developers.asana.com/reference/getteamsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get teams from.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,description,html_description")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               A list of team records containing:
     *               - gid: Team identifier
     *               - name: Team name
     *               - resource_type: Always "team"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing team list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getTeamsInWorkspace(
        string $workspaceGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/teams",
            ['query' => $options],
            $fullResponse
        );
    }

    /**
     * Get all projects in a workspace
     *
     * Returns the compact project records for all projects in the workspace.
     * Returns projects the authenticated user has access to.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get projects from.
     *                           This identifier can be found in the workspace URL or returned from
     *                           workspace-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - archived (boolean): Only return projects whose archived field matches this value
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               A list of project records containing:
     *               - gid: Project identifier
     *               - name: Project name
     *               - resource_type: Always "project"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsInWorkspace(
        string $workspaceGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/projects",
            ['query' => $options],
            $fullResponse
        );
    }

    /**
     * Search tasks in a workspace
     *
     * Search for tasks within a specific workspace. Results are returned based on the search criteria and
     * permissions of the user making the request.
     *
     * API Documentation: https://developers.asana.com/reference/searchtasksforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to search in.
     * @param array $options Query parameters to filter and customize search results. Supported keys include:
     *   - text (string): Full-text search query
     *   - resource_subtype (string): Filter by task type (e.g., "default_task", "milestone", "section")
     *   - assignee.any (array): GIDs of users tasks could be assigned to
     *   - assignee.not (array): GIDs of users tasks should not be assigned to
     *   - projects.any (array): GIDs of projects tasks could be in
     *   - sections.any (array): GIDs of sections tasks could be in
     *   - completed (boolean): Filter by completion status
     *   - modified_on.before (string): ISO 8601 datetime tasks were modified before
     *   - modified_on.after (string): ISO 8601 datetime tasks were modified after
     *   - created_on.before (string): ISO 8601 datetime tasks were created before
     *   - created_on.after (string): ISO 8601 datetime tasks were created after
     *   - completed_on.before (string): ISO 8601 datetime tasks were completed before
     *   - completed_on.after (string): ISO 8601 datetime tasks were completed after
     *   - due_on.before (string): ISO 8601 date tasks are due before
     *   - due_on.after (string): ISO 8601 date tasks are due after
     *   - due_on (string): ISO 8601 date tasks are due on
     *   - start_on (string): ISO 8601 date tasks start on
     *   - opt_fields (string): Comma-separated fields to include in results
     *   - limit (int): Maximum number of results to return (1-100)
     *   - offset (string): Pagination offset token
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               A list of task records matching the search criteria:
     *               - gid: Task identifier
     *               - name: Task name
     *               - resource_type: Always "task"
     *               Additional fields if specified in opt_fields
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing task list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, invalid search parameters,
     *                          permission errors, network issues, or rate limiting occurs
     */
    public function searchTasksInWorkspace(
        string $workspaceGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/tasks/search",
            ['query' => $options],
            $fullResponse
        );
    }

    /**
     * Get workspace events
     *
     * Returns events for a single workspace.
     * This endpoint supports retrieval of either all events in a workspace (for synchronization)
     * or a filtered subset of events from a resource in a workspace.
     *
     * Important note: Currently, access to this API is exclusively available through a service account
     * in an Enterprise+ domain. To get started, see the workspace events guide.
     *
     * API Documentation: https://developers.asana.com/reference/getworkspaceevents
     *
     * @param string $workspaceGid The unique global ID of the workspace to get events from.
     *                             This identifier can be found in the workspace URL or returned from
     *                             workspace-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - sync (string): A sync token received from a previous call to this endpoint.
     *                                       If provided, only events since the token will be returned.
     *                      - opt_pretty (bool): Returns formatted JSON if true.
     * @param bool $fullResponse Whether to return the full response details or just the decoded response body.
     *
     * @return array If $fullResponse is false:
     *               - data: Array of event objects
     *               - sync: Sync token to be used in subsequent requests
     *               If $fullResponse is true:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing events data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getWorkspaceEvents(
        string $workspaceGid,
        array $options = [],
        bool $fullResponse = false
    ): array {
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/events",
            ['query' => $options],
            $fullResponse
        );
    }
}
