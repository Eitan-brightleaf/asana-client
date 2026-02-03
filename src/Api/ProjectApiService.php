<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class ProjectApiService
{
    use ValidationTrait;

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
     * Get multiple projects
     *
     * GET /projects
     *
     * Returns a list of projects in a workspace or team that the user has access to.
     * This endpoint provides a way to get multiple projects in a single request according
     * to your search parameters.
     *
     * API Documentation: https://developers.asana.com/reference/getprojects
     *
     * @param string|null $workspace Filter projects by workspace. Can be workspace ID or null.
     *                               This or $team must have a value.
     *                               Example: "12345"
     * @param string|null $team Filter projects by team. Can be team ID or null.
     *                          This or $workspace must have a value.
     *                          Example: "67890"
     * @param array $options Query parameters to filter and format results:
     *                      Filtering parameters:
     *                      - archived (boolean): Only return projects whose archived field takes this value
     *                      - limit (int): Maximum number of projects to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of projects with fields including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details (if applicable)
     *                 - archived: Boolean indicating if project is archived
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid parameter values
     *                          - Insufficient permissions
     *                          - Rate limiting
     *                          - Network connectivity issues
     */
    public function getProjects(
        ?string $workspace = null,
        ?string $team = null,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        // Ensure one of workspace or team is provided
        if (!$workspace && !$team) {
            throw new InvalidArgumentException('You must provide either a "workspace" or "team" parameter.');
        }

        // Add the provided identifier to options
        if ($workspace) {
            $options['workspace'] = $workspace;
        }
        if ($team) {
            $options['team'] = $team;
        }

        return $this->client->request('GET', 'projects', ['query' => $options], $responseType);
    }

    /**
     * Create a project
     *
     * POST /projects
     *
     * Creates a new project in an Asana workspace or team. The project can be given a name,
     * notes, specified team/workspace, and other configurable attributes.
     *
     * API Documentation: https://developers.asana.com/reference/createproject
     *
     * @param array $data Data for creating the project. Supported fields include:
     *                    Required (at least one of):
     *                    - workspace (string): GID of workspace to create project in.
     *                      Example: "12345"
     *                    - team (string): GID of team to create project in.
     *                      Example: "67890"
     *                    Optional:
     *                    - name (string): Name of the project.
     *                      Example: "Marketing Campaign"
     *                    - notes (string): Project description/notes.
     *                      Example: "Q4 marketing campaign project"
     *                    - color (string): Color of the project. Available colors: "dark-pink", "dark-green",
     *                      "dark-blue", "dark-red", "dark-teal", "dark-brown", "dark-orange", "dark-purple",
     *                      "dark-warm-gray", "light-pink", "light-green", "light-blue", "light-red",
     *                      "light-teal", "light-brown", "light-orange", "light-purple", "light-warm-gray"
     *                    - due_date (string): Due date in YYYY-MM-DD format.
     *                      Example: "2024-12-31"
     *                    - public (boolean): Whether the project is public to the organization
     *                    - default_view (string): Default view for the project. Options: "list", "board",
     *                      "timeline", "calendar"
     *                    Example: ["name" => "New Project", "workspace" => "12345", "notes" => "Project details"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created project details including:
     *                 - gid: Unique identifier of the created project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - notes: Project description/notes
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details (if applicable)
     *                 - color: Color of the project
     *                 - due_date: Due date of the project
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Missing required fields
     *                          - Invalid field values
     *                          - Insufficient permissions
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function createProject(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            'projects',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get a project
     *
     * GET /projects/{project_gid}
     *
     * Returns the complete project record for a single project. The project record includes
     * basic metadata (name, notes, status, etc.) along with any custom fields and more
     * as requested via opt_fields.
     *
     * API Documentation: https://developers.asana.com/reference/getproject
     *
     * @param string $projectGid The unique global ID of the project to retrieve. This identifier
     *                           can be found in the project URL or returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                        Common fields include: name, notes, owner, workspace, team, members, followers,
     *                        created_at, modified_at, due_date, current_status, color, public, archived
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the project details including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - notes: Project description/notes
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details (if applicable)
     *                 - members: Array of project member objects
     *                 - followers: Array of project follower objects
     *                 - color: Color of the project
     *                 - due_date: Due date of the project
     *                 - current_status: Object containing current project status
     *                 - archived: Boolean indicating if project is archived
     *                 - public: Boolean indicating if project is public
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to invalid project GID, insufficient permissions,
     *                          network issues, or rate limiting occurs
     * @throws InvalidArgumentException If project GID is empty
     */
    public function getProject(
        string $projectGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request('GET', "projects/$projectGid", ['query' => $options], $responseType);
    }

    /**
     * Update a project
     *
     * PUT /projects/{project_gid}
     *
     * Updates the properties of a project. Projects can be updated to change things like their name,
     * notes, due date, and other properties. Any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updateproject
     *
     * @param string $projectGid The unique global ID of the project to update.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $data The properties of the project to update. Can include:
     *                    - name (string): Name of the project.
     *                      Example: "Updated Project Name"
     *                    - notes (string): Project description/notes.
     *                      Example: "Updated project description"
     *                    - color (string): Color of the project. Available colors: "dark-pink", "dark-green",
     *                      "dark-blue", "dark-red", "dark-teal", "dark-brown", "dark-orange", "dark-purple",
     *                      "dark-warm-gray", "light-pink", "light-green", "light-blue", "light-red",
     *                      "light-teal", "light-brown", "light-orange", "light-purple", "light-warm-gray"
     *                    - due_date (string): Due date in YYYY-MM-DD format.
     *                      Example: "2024-12-31"
     *                    - public (boolean): Whether the project is public to the organization
     *                    - archived (boolean): Whether the project is archived
     *                    - default_view (string): Default view for the project. Options: "list", "board",
     *                      "timeline", "calendar"
     *                    Example: ["name" => "Updated Project", "notes" => "New description"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing updated project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated project details including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Updated name of the project
     *                 - notes: Updated project description/notes
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details (if applicable)
     *                 - color: Updated color of the project
     *                 - due_date: Updated due date of the project
     *                 - current_status: Object containing current project status
     *                 - archived: Updated archived status
     *                 - public: Updated public status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateProject(
        string $projectGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'PUT',
            "projects/$projectGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a project
     *
     * DELETE /projects/{project_gid}
     *
     * Deletes a project. This endpoint may return with a success code before the project has been
     * completely deleted. Also note that while you can delete a single-owner project, you must be an
     * admin in the workspace that contains the project to delete a multi-owned project.
     *
     * API Documentation: https://developers.asana.com/reference/deleteproject
     *
     * @param string $projectGid The unique global ID of the project to delete/trash.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful deletion
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid project GID
     *                          - Insufficient permissions to delete the project
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteProject(string $projectGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request('DELETE', "projects/$projectGid", [], $responseType);
    }

    /**
     * Duplicate a project
     *
     * POST /projects/{project_gid}/duplicate
     *
     * Creates and returns a job that will duplicate a project, copying its tasks, sections, and structure.
     * The project must be in a premium workspace to use this capability.
     *
     * API Documentation: https://developers.asana.com/reference/duplicateproject
     *
     * @param string $projectGid The unique global ID of the project to duplicate.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $data Data for the duplicated project. Supported fields include:
     *                    Required:
     *                    - name (string): The name of the new project.
     *                      Example: "Duplicated Project Name"
     *                    Optional:
     *                    - include (array): The elements that will be duplicated to the new project.
     *                      Possible values: "members", "notes", "forms", "task_notes", "task_assignee",
     *                      "task_subtasks", "task_attachments", "task_dates", "task_dependencies",
     *                      "task_followers", "task_tags", "task_projects"
     *                      Example: ["members", "notes", "task_assignee"]
     *                    - schedule_dates (object): A mapping of date fields to new values for the duplicated project.
     *                      Example: {"due_date": "2024-12-31", "start_date": "2024-01-01"}
     *                    Example: ["name" => "New Project Copy", "include" => ["members", "notes"]]
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing job data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the job details including:
     *                 - gid: Unique identifier of the duplication job
     *                 - resource_type: Always "job"
     *                 - status: Current status of the job ("not_started", "in_progress", "succeeded", "failed")
     *                 - new_project: Object containing the new project details once duplication is complete
     *
     * @throws AsanaApiException If the API request fails due to invalid project GID, malformed data,
     *                          insufficient permissions, network issues, or rate limiting
     */
    public function duplicateProject(
        string $projectGid,
        array $data,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/duplicate",
            ['json' => ['data' => $data]],
            $responseType
        );
    }

    /**
     * Get projects a task is in
     *
     * GET /tasks/{task_gid}/projects
     *
     * Returns a list of projects that the specified task is a member of. A task can
     * be associated with multiple projects.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsfortask
     *
     * @param string $taskGid The unique global ID of the task to get projects for.
     *                        This identifier can be found in the task URL or
     *                        returned from task-related API endpoints.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of projects to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of projects with fields including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details (if applicable)
     *                 - archived: Boolean indicating if project is archived
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid task GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsForTask(
        string $taskGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($taskGid, 'Task GID');

        return $this->client->request('GET', "tasks/$taskGid/projects", ['query' => $options], $responseType);
    }

    /**
     * Get a team's projects
     *
     * GET /teams/{team_gid}/projects
     *
     * Returns the projects in a team. Teams are only available on Asana Premium or Business plans.
     * This endpoint requires the team to be public to the authenticated user or for the user to be an
     * admin of the team.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsforteam
     *
     * @param string $teamGid The unique global ID of the team to get projects from.
     *                        This identifier can be found in the team URL or
     *                        returned from team-related API endpoints.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - archived (boolean): Only return projects whose archived field takes this value
     *                      - limit (int): Maximum number of projects to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of projects with fields including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details
     *                 - archived: Boolean indicating if project is archived
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid team GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsForTeam(
        string $teamGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');

        return $this->client->request('GET', "teams/$teamGid/projects", ['query' => $options], $responseType);
    }

    /**
     * Create a project in a team
     *
     * POST /teams/{team_gid}/projects
     *
     * Creates a project and adds it to the specified team. This endpoint creates a project from
     * scratch, setting its workspace to be the same workspace containing the team.
     *
     * API Documentation: https://developers.asana.com/reference/createprojectforteam
     *
     * @param string $teamGid The unique global ID of the team in which to create the project.
     *                        This identifier can be found in the team URL or returned from
     *                        team-related API endpoints.
     *                        Example: "12345"
     * @param array $data Data for creating the project. Supported fields include:
     *                    Optional:
     *                    - name (string): Name of the project.
     *                      Example: "Team Project"
     *                    - notes (string): Project description/notes.
     *                      Example: "Project for team collaboration"
     *                    - color (string): Color of the project. Available colors: "dark-pink", "dark-green",
     *                      "dark-blue", "dark-red", "dark-teal", "dark-brown", "dark-orange", "dark-purple",
     *                      "dark-warm-gray", "light-pink", "light-green", "light-blue", "light-red",
     *                      "light-teal", "light-brown", "light-orange", "light-purple", "light-warm-gray"
     *                    - due_date (string): Due date in YYYY-MM-DD format.
     *                      Example: "2024-12-31"
     *                    - public (boolean): Whether the project is public to the organization
     *                    - default_view (string): Default view for the project. Options: "list", "board",
     *                      "timeline", "calendar"
     *                    Example: ["name" => "New Team Project", "notes" => "Project details"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created project details including:
     *                 - gid: Unique identifier of the created project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - notes: Project description/notes
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details
     *                 - color: Color of the project
     *                 - due_date: Due date of the project
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid team GID provided, missing required fields,
     *                          insufficient permissions, or network issues occur
     */
    public function createProjectInTeam(
        string $teamGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');

        return $this->client->request(
            'POST',
            "teams/$teamGid/projects",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get all projects in a workspace
     *
     * GET /workspaces/{workspace_gid}/projects
     *
     * Returns the projects in a workspace. Includes archived projects by default.
     *
     * API Documentation: https://developers.asana.com/reference/getprojectsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get projects from.
     *                             This identifier can be found in the workspace URL or
     *                             returned from workspace-related API endpoints.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - archived (boolean): Only return projects whose archived field takes this value
     *                      - limit (int): Maximum number of projects to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of projects with fields including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - team: Object containing team details (if applicable)
     *                 - archived: Boolean indicating if project is archived
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getProjectsForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        return $this->client->request('GET', "workspaces/$workspaceGid/projects", ['query' => $options], $responseType);
    }

    /**
     * Create a project in a workspace
     *
     * POST /workspaces/{workspace_gid}/projects
     *
     * Creates a project in the specified workspace.
     *
     * API Documentation: https://developers.asana.com/reference/createproject
     *
     * @param string $workspaceGid The unique global ID of the workspace to create the project in.
     *                             This identifier can be found in the workspace URL or
     *                             returned from workspace-related API endpoints.
     *                             Example: "12345"
     * @param array $data Data for creating the project. Supported fields include:
     *                    Optional:
     *                    - name (string): Name of the project.
     *                      Example: "Workspace Project"
     *                    - notes (string): Project description/notes.
     *                      Example: "Project for workspace collaboration"
     *                    - color (string): Color of the project. Available colors: "dark-pink", "dark-green",
     *                      "dark-blue", "dark-red", "dark-teal", "dark-brown", "dark-orange", "dark-purple",
     *                      "dark-warm-gray", "light-pink", "light-green", "light-blue", "light-red",
     *                      "light-teal", "light-brown", "light-orange", "light-purple", "light-warm-gray"
     *                    - due_date (string): Due date in YYYY-MM-DD format.
     *                      Example: "2024-12-31"
     *                    - public (boolean): Whether the project is public to the organization
     *                    - default_view (string): Default view for the project. Options: "list", "board",
     *                      "timeline", "calendar"
     *                    Example: ["name" => "New Workspace Project", "notes" => "Project details"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,custom_field_settings,due_date,current_status")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created project details including:
     *                 - gid: Unique identifier of the created project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - notes: Project description/notes
     *                 - owner: Object containing project owner details
     *                 - workspace: Object containing workspace details
     *                 - color: Color of the project
     *                 - due_date: Due date of the project
     *                 - current_status: Object containing current project status
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, missing required fields,
     *                          insufficient permissions, or network issues occur
     */
    public function createProjectInWorkspace(
        string $workspaceGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        return $this->client->request(
            'POST',
            "workspaces/$workspaceGid/projects",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add a custom field to a project
     *
     * POST /projects/{project_gid}/addCustomFieldSetting
     *
     * Adds a custom field to a project. Custom fields are defined per-organization and must exist
     * before they can be added to a project. By default, a custom field in a project may not be
     * associated with another project in the same organization, but this can be controlled by the project.
     *
     * API Documentation: https://developers.asana.com/reference/addcustomfieldsettingforproject
     *
     * @param string $projectGid The unique global ID of the project to add the custom field to.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $data Data for adding the custom field setting. Supported fields include:
     *                    Required:
     *                    - custom_field (string): The GID of the custom field to add to the project.
     *                      Example: "67890"
     *                    Optional:
     *                    - is_important (boolean): Whether this custom field is considered important for the project.
     *                      Important custom fields are displayed prominently in the project view.
     *                    - insert_before (string): GID of the custom field setting to insert this new setting before.
     *                    - insert_after (string): GID of the custom field setting to insert this new setting after.
     *                    Example: ["custom_field" => "67890", "is_important" => true]
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing custom field setting data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the custom field setting details including:
     *                 - gid: Unique identifier of the custom field setting
     *                 - resource_type: Always "custom_field_setting"
     *                 - custom_field: Object containing custom field details
     *                 - is_important: Boolean indicating if the custom field is important
     *                 - project: Object containing project details
     *
     * @throws AsanaApiException If invalid project GID provided, invalid custom field GID,
     *                          insufficient permissions, or network issues occur
     */
    public function addCustomFieldToProject(
        string $projectGid,
        array $data,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/addCustomFieldSetting",
            ['json' => ['data' => $data]],
            $responseType
        );
    }

    /**
     * Remove a custom field from a project
     *
     * POST /projects/{project_gid}/removeCustomFieldSetting
     *
     * Removes a custom field from a project. Note that this does not delete the custom field,
     * it just removes the custom field from the specified project.
     *
     * API Documentation: https://developers.asana.com/reference/removecustomfieldsettingforproject
     *
     * @param string $projectGid The unique global ID of the project to remove the custom field from.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $data Data for removing the custom field setting. Supported fields include:
     *                    Required:
     *                    - custom_field (string): The GID of the custom field to remove from the project.
     *                      Example: "67890"
     *                    Example: ["custom_field" => "67890"]
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful removal
     *
     * @throws AsanaApiException If invalid project GID provided, invalid custom field GID,
     *                          insufficient permissions, or network issues occur
     */
    public function removeCustomFieldFromProject(
        string $projectGid,
        array $data,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/removeCustomFieldSetting",
            ['json' => ['data' => $data]],
            $responseType
        );
    }

    /**
     * Get a project's custom fields
     *
     * GET /projects/{project_gid}/custom_field_settings
     *
     * Returns a list of all the custom fields settings on a project, in compact form.
     * These are custom fields that the project has direct access to and can be seen
     * in the project's details.
     *
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforproject
     *
     * @param string $projectGid The unique global ID of the project to get custom field settings from.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of custom field settings to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "gid,custom_field.name,is_important,project")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing custom field settings data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of custom field settings with fields including:
     *                 - gid: Unique identifier of the custom field setting
     *                 - resource_type: Always "custom_field_setting"
     *                 - custom_field: Object containing custom field details
     *                 - is_important: Boolean indicating if the custom field is important
     *                 - project: Object containing project details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getCustomFieldsForProject(
        string $projectGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'GET',
            "projects/$projectGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get task count of a project
     *
     * GET /projects/{project_gid}/task_counts
     *
     * Returns the number of tasks within the specified project, grouped by completion status.
     * This is useful for understanding project progress and workload.
     *
     * API Documentation: https://developers.asana.com/reference/gettaskcountsforproject
     *
     * @param string $projectGid The unique global ID of the project to get task counts for.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "num_completed_tasks,num_incomplete_tasks,num_tasks")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing task count data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the task count details including:
     *                 - num_tasks: Total number of tasks in the project
     *                 - num_completed_tasks: Number of completed tasks in the project
     *                 - num_incomplete_tasks: Number of incomplete tasks in the project
     *                 - num_milestones: Number of milestones in the project
     *                 - num_completed_milestones: Number of completed milestones in the project
     *                 - num_incomplete_milestones: Number of incomplete milestones in the project
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTaskCountsForProject(
        string $projectGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request('GET', "projects/$projectGid/task_counts", ['query' => $options], $responseType);
    }

    /**
     * Add users to a project
     *
     * POST /projects/{project_gid}/addMembers
     *
     * Adds the specified list of users as members of the project. Users are
     * immediately able to collaborate on the project, get notifications, and
     * gain access to the project based on their role.
     *
     * API Documentation: https://developers.asana.com/reference/addmembersforproject
     *
     * @param string $projectGid The unique global ID of the project to add members to.
     *                           This identifier can be found in the project URL or returned from
     *                           project-related API endpoints.
     *                           Example: "12345"
     * @param array $members An array of user GIDs representing the users to add to the project.
     *                       Each element should be a string containing a user GID.
     *                       Example: ["67890", "11111", "22222"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,members.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated project details including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - members: Array of member objects including the newly added members
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function addMembersToProject(
        string $projectGid,
        array $members,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/addMembers",
            ['json' => ['data' => ['members' => $members]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove users from a project
     *
     * POST /projects/{project_gid}/removeMembers
     *
     * Removes the specified list of users as members of the project. Users will
     * immediately lose access to the project and will no longer receive notifications
     * unless they remain added as followers.
     *
     * API Documentation: https://developers.asana.com/reference/removemembersforproject
     *
     * @param string $projectGid The unique global ID of the project from which to remove members.
     *                           This identifier can be found in the project URL or returned from
     *                           project-related API endpoints.
     *                           Example: "12345"
     * @param array $members An array of user GIDs representing the users to remove from the project.
     *                       Each element should be a string containing a user GID.
     *                       Example: ["67890", "11111", "22222"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,members.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated project details including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - members: Array of member objects with the specified members removed
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function removeMembersFromProject(
        string $projectGid,
        array $members,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/removeMembers",
            ['json' => ['data' => ['members' => $members]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add followers to a project
     *
     * POST /projects/{project_gid}/addFollowers
     *
     * Adds the specified list of users as followers of the project. Followers receive notifications
     * when the project is changed, but do not necessarily have permissions to modify the project.
     *
     * API Documentation: https://developers.asana.com/reference/addfollowersforproject
     *
     * @param string $projectGid The unique global ID of the project to add followers to.
     *                           This identifier can be found in the project URL or returned from
     *                           project-related API endpoints.
     *                           Example: "12345"
     * @param array $followers An array of user GIDs representing the followers to add to the project.
     *                         Each element should be a string containing a user GID.
     *                         Example: ["67890", "11111", "22222"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,followers.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated project details including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - followers: Array of follower objects including the newly added followers
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function addFollowersToProject(
        string $projectGid,
        array $followers,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/addFollowers",
            ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove followers from a project
     *
     * POST /projects/{project_gid}/removeFollowers
     *
     * Removes the specified list of users from following the project. Followers receive notifications
     * when the project is changed, and removing them will stop these notifications.
     *
     * API Documentation: https://developers.asana.com/reference/removefollowersforproject
     *
     * @param string $projectGid The unique global ID of the project from which to remove followers.
     *                           This identifier can be found in the project URL or returned from
     *                           project-related API endpoints.
     *                           Example: "12345"
     * @param array $followers An array of user GIDs representing the followers to remove from the project.
     *                         Each element should be a string containing a user GID.
     *                         Example: ["67890", "11111", "22222"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner.name,followers.name")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated project details including:
     *                 - gid: Unique identifier of the project
     *                 - resource_type: Always "project"
     *                 - name: Name of the project
     *                 - followers: Array of follower objects with the specified followers removed
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function removeFollowersFromProject(
        string $projectGid,
        array $followers,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/removeFollowers",
            ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Create a project template from a project
     *
     * POST /projects/{project_gid}/saveAsTemplate
     *
     * Creates a project template from an existing project. The new template will be in the same
     * workspace as the given project. Properties such as task names, descriptions, notes,
     * assignees, dependencies, and custom fields are preserved in the template.
     *
     * API Documentation: https://developers.asana.com/reference/projectsaveastemplate
     *
     * @param string $projectGid The unique global ID of the project to use as a basis for creating a template.
     *                           This identifier can be found in the project URL or
     *                           returned from project-related API endpoints.
     *                           Example: "12345"
     * @param array $data Data for creating the project template. Supported fields include:
     *                    Required:
     *                    - name (string): The name of the new project template.
     *                      Example: "Marketing Campaign Template"
     *                    Optional:
     *                    - description (string): Free-form textual information associated with the template.
     *                      Example: "Template for quarterly marketing campaigns"
     *                    - public (boolean): Whether the template should be public to the organization.
     *                    Example: ["name" => "My Project Template", "description" => "Template description"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "gid,name,description,public")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing project template data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created project template details including:
     *                 - gid: Unique identifier of the created project template
     *                 - resource_type: Always "project_template"
     *                 - name: Name of the project template
     *                 - description: Description of the project template
     *                 - public: Boolean indicating if the template is public
     *                 - workspace: Object containing workspace details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid project GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function createProjectTemplateFromProject(
        string $projectGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'POST',
            "projects/$projectGid/saveAsTemplate",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }
}
