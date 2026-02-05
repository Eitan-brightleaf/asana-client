<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class GoalsApiService
{
    use ValidationTrait;

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
     * Get multiple goals
     *
     * GET /goals
     *
     * Returns compact goal records filtered by the given criteria. You must specify at least
     * one of portfolio, project, team, or workspace to filter goals.
     *
     * API Documentation: https://developers.asana.com/reference/getgoals
     *
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - portfolio (string): GID of a portfolio to filter goals from
     *                      - project (string): GID of a project to filter goals from
     *                      - team (string): GID of a team to filter goals from
     *                      - workspace (string): GID of a workspace to filter goals from
     *                      - time_periods (array): Array of time period GIDs to filter by
     *                      - is_workspace_level (bool): Filter to workspace-level goals
     *                      Pagination parameters:
     *                      - limit (int): Maximum number of goals to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body containing goal data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of goals with fields including:
     *                 - gid: Unique identifier of the goal
     *                 - resource_type: Always "goal"
     *                 - name: Name of the goal
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function getGoals(
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', 'goals', ['query' => $options], $responseType);
    }

    /**
     * Get a goal
     *
     * GET /goals/{goal_gid}
     *
     * Returns the full record for a single goal.
     *
     * API Documentation: https://developers.asana.com/reference/getgoal
     *
     * @param string $goalGid The unique global ID of the goal to retrieve.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner,workspace,due_on,start_on,status,liked,num_likes")
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
     *               - body: Decoded response body containing goal data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the goal details including:
     *                 - gid: Unique identifier of the goal
     *                 - resource_type: Always "goal"
     *                 - name: Name of the goal
     *                 - owner: Object containing the owner details
     *                 - workspace: Object containing the workspace details
     *                 - due_on: Due date
     *                 - start_on: Start date
     *                 - status: Status of the goal
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid goal GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getGoal(
        string $goalGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request('GET', "goals/$goalGid", ['query' => $options], $responseType);
    }

    /**
     * Create a goal
     *
     * POST /goals
     *
     * Creates a new goal in the specified workspace. Returns the full record of the
     * newly created goal.
     *
     * API Documentation: https://developers.asana.com/reference/creategoal
     *
     * @param array $data Data for creating the goal. Supported fields include:
     *                    Required:
     *                    - name (string): Name of the goal.
     *                      Example: "Increase revenue by 20%"
     *                    - workspace (string): GID of the workspace to create the goal in.
     *                      Example: "12345"
     *                    Optional:
     *                    - due_on (string): Due date in YYYY-MM-DD format
     *                    - start_on (string): Start date in YYYY-MM-DD format
     *                    - owner (string): GID of the user who owns the goal
     *                    - team (string): GID of the team the goal belongs to
     *                    - time_period (string): GID of the time period for the goal
     *                    - liked (bool): Whether the goal is liked by the current user
     *                    - is_workspace_level (bool): Whether this is a workspace-level goal
     *                    - notes (string): Free-form textual notes about the goal
     *                    Example: ["name" => "Increase revenue by 20%", "workspace" => "12345"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body containing created goal data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created goal details
     *
     * @throws InvalidArgumentException If required fields (name, workspace) are missing
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function createGoal(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['name', 'workspace'], 'goal creation');

        return $this->client->request(
            'POST',
            'goals',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Update a goal
     *
     * PUT /goals/{goal_gid}
     *
     * Updates the properties of a goal. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updategoal
     *
     * @param string $goalGid The unique global ID of the goal to update.
     *                        Example: "12345"
     * @param array $data The properties of the goal to update. Can include:
     *                    - name (string): Name of the goal
     *                    - due_on (string): Due date in YYYY-MM-DD format
     *                    - start_on (string): Start date in YYYY-MM-DD format
     *                    - owner (string): GID of the user who owns the goal
     *                    - status (string): Status of the goal
     *                    - liked (bool): Whether the goal is liked
     *                    - notes (string): Free-form textual notes about the goal
     *                    Example: ["name" => "Updated Goal Name", "status" => "on_track"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body containing updated goal data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated goal details
     *
     * @throws AsanaApiException If invalid goal GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateGoal(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'PUT',
            "goals/$goalGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a goal
     *
     * DELETE /goals/{goal_gid}
     *
     * Deletes the specified goal. This action is permanent and cannot be undone.
     *
     * API Documentation: https://developers.asana.com/reference/deletegoal
     *
     * @param string $goalGid The unique global ID of the goal to delete.
     *                        Example: "12345"
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
     *                          - Invalid goal GID
     *                          - Goal not found
     *                          - Insufficient permissions to delete the goal
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteGoal(string $goalGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request('DELETE', "goals/$goalGid", [], $responseType);
    }

    /**
     * Get parent goals for a goal
     *
     * GET /goals/{goal_gid}/parentGoals
     *
     * Returns the compact records for all parent goals of the given goal.
     *
     * API Documentation: https://developers.asana.com/reference/getparentgoalsforgoal
     *
     * @param string $goalGid The unique global ID of the goal.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner,workspace")
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
     *               - body: Decoded response body containing parent goals data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of parent goals
     *
     * @throws AsanaApiException If invalid goal GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getParentGoalsForGoal(
        string $goalGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'GET',
            "goals/$goalGid/parentGoals",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Add a subgoal to a goal
     *
     * POST /goals/{goal_gid}/addSubgoal
     *
     * Adds a subgoal to the specified parent goal.
     *
     * API Documentation: https://developers.asana.com/reference/addsubgoalforgoal
     *
     * @param string $goalGid The unique global ID of the parent goal.
     *                        Example: "12345"
     * @param array $data Data for adding the subgoal. Supported fields include:
     *                    Required:
     *                    - subgoal (string): The GID of the goal to add as a subgoal.
     *                      Example: "67890"
     *                    Optional:
     *                    - insert_before (string): GID of the subgoal to insert before
     *                    - insert_after (string): GID of the subgoal to insert after
     *                    Example: ["subgoal" => "67890"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful addition
     *
     * @throws InvalidArgumentException If the goal GID is invalid or subgoal field is missing
     * @throws AsanaApiException If the subgoal doesn't exist, insufficient permissions,
     *                          or network issues occur
     */
    public function addSubgoal(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        $this->validateRequiredFields($data, ['subgoal'], 'adding subgoal to goal');

        return $this->client->request(
            'POST',
            "goals/$goalGid/addSubgoal",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove a subgoal from a goal
     *
     * POST /goals/{goal_gid}/removeSubgoal
     *
     * Removes a subgoal from the specified parent goal.
     *
     * API Documentation: https://developers.asana.com/reference/removesubgoalforgoal
     *
     * @param string $goalGid The unique global ID of the parent goal.
     *                        Example: "12345"
     * @param array $data Data for removing the subgoal. Supported fields include:
     *                    Required:
     *                    - subgoal (string): The GID of the goal to remove as a subgoal.
     *                      Example: "67890"
     *                    Example: ["subgoal" => "67890"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful removal
     *
     * @throws InvalidArgumentException If the goal GID is invalid or subgoal field is missing
     * @throws AsanaApiException If the subgoal doesn't exist in goal, insufficient permissions,
     *                          or network issues occur
     */
    public function removeSubgoal(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        $this->validateRequiredFields($data, ['subgoal'], 'removing subgoal from goal');

        return $this->client->request(
            'POST',
            "goals/$goalGid/removeSubgoal",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add supporting work for a goal
     *
     * POST /goals/{goal_gid}/addSupportingWork
     *
     * Adds a supporting project or portfolio to the specified goal.
     *
     * API Documentation: https://developers.asana.com/reference/addsupportingworkforgoal
     *
     * @param string $goalGid The unique global ID of the goal.
     *                        Example: "12345"
     * @param array $data Data for adding the supporting work. Supported fields include:
     *                    Required:
     *                    - supporting_work (string): The GID of the project or portfolio to add as supporting work.
     *                      Example: "67890"
     *                    Example: ["supporting_work" => "67890"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful addition
     *
     * @throws InvalidArgumentException If the goal GID is invalid or supporting_work field is missing
     * @throws AsanaApiException If the supporting work doesn't exist, insufficient permissions,
     *                          or network issues occur
     */
    public function addSupportingWorkForGoal(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        $this->validateRequiredFields($data, ['supporting_work'], 'adding supporting work to goal');

        return $this->client->request(
            'POST',
            "goals/$goalGid/addSupportingWork",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove supporting work for a goal
     *
     * POST /goals/{goal_gid}/removeSupportingWork
     *
     * Removes a supporting project or portfolio from the specified goal.
     *
     * API Documentation: https://developers.asana.com/reference/removesupportingworkforgoal
     *
     * @param string $goalGid The unique global ID of the goal.
     *                        Example: "12345"
     * @param array $data Data for removing the supporting work. Supported fields include:
     *                    Required:
     *                    - supporting_work (string): The GID of the project or portfolio to remove.
     *                      Example: "67890"
     *                    Example: ["supporting_work" => "67890"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
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
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful removal
     *
     * @throws InvalidArgumentException If the goal GID is invalid or supporting_work field is missing
     * @throws AsanaApiException If the supporting work doesn't exist in goal, insufficient permissions,
     *                          or network issues occur
     */
    public function removeSupportingWorkForGoal(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        $this->validateRequiredFields($data, ['supporting_work'], 'removing supporting work from goal');

        return $this->client->request(
            'POST',
            "goals/$goalGid/removeSupportingWork",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }
}
