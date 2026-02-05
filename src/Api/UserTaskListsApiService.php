<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class UserTaskListsApiService
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
     * Get a user task list
     *
     * GET /user_task_lists/{user_task_list_gid}
     *
     * Returns the full record for a user task list ("My Tasks").
     *
     * API Documentation: https://developers.asana.com/reference/getusertasklist
     *
     * @param string $userTaskListGid The unique global ID of the user task list.
     *                                Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "name,owner,workspace")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing user task list data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the user task list details including:
     *                 - gid: Unique identifier of the user task list
     *                 - resource_type: Always "user_task_list"
     *                 - name: Name of the user task list (e.g., "My Tasks")
     *                 - owner: Object containing the owner details
     *                 - workspace: Object containing the workspace details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getUserTaskList(
        string $userTaskListGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($userTaskListGid, 'User Task List GID');

        return $this->client->request(
            'GET',
            "user_task_lists/$userTaskListGid",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get a user's task list ("My Tasks") for a workspace
     *
     * GET /users/{user_gid}/user_task_list
     *
     * Returns the full record for a user's task list in the given workspace.
     * Each user has exactly one task list per workspace.
     *
     * API Documentation: https://developers.asana.com/reference/getusertasklistforuser
     *
     * @param string $userGid The unique global ID of the user. Can also be the string "me"
     *                        to refer to the current authenticated user.
     *                        Example: "12345"
     * @param string $workspaceGid The unique global ID of the workspace.
     *                             Example: "67890"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "name,owner,workspace")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                              - AsanaApiClient::RESPONSE_FULL (1): Full response
     *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *               If $responseType is AsanaApiClient::RESPONSE_FULL:
     *               - status: HTTP status code
     *               - reason: Response status message
     *               - headers: Response headers
     *               - body: Decoded response body containing user task list data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the user task list details including:
     *                 - gid: Unique identifier of the user task list
     *                 - resource_type: Always "user_task_list"
     *                 - name: Name of the user task list
     *                 - owner: Object containing the owner details
     *                 - workspace: Object containing the workspace details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GIDs provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getUserTaskListForUser(
        string $userGid,
        string $workspaceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($userGid, 'User GID');
        $this->validateGid($workspaceGid, 'Workspace GID');

        $options['workspace'] = $workspaceGid;

        return $this->client->request(
            'GET',
            "users/$userGid/user_task_list",
            ['query' => $options],
            $responseType
        );
    }
}
