<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class TeamsApiService
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
     * Get a team
     *
     * GET /teams/{team_gid}
     *
     * Returns the full record for a single team.
     *
     * API Documentation: https://developers.asana.com/reference/getteam
     *
     * @param string $teamGid The unique global ID of the team to retrieve.
     *                        This identifier can be found in the team URL or
     *                        returned from team-related API endpoints.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,description,organization,permalink_url")
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
     *               - body: Decoded response body containing team data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the team details including:
     *                 - gid: Unique identifier of the team
     *                 - resource_type: Always "team"
     *                 - name: Name of the team
     *                 - description: Description of the team
     *                 - html_description: HTML formatted description
     *                 - organization: Object containing the organization details
     *                 - permalink_url: URL to the team in Asana
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid team GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTeam(
        string $teamGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');

        return $this->client->request('GET', "teams/$teamGid", ['query' => $options], $responseType);
    }

    /**
     * Get teams for a user
     *
     * GET /users/{user_gid}/teams
     *
     * Returns the compact records for all teams to which the given user belongs
     * within the specified organization.
     *
     * API Documentation: https://developers.asana.com/reference/getteamsforuser
     *
     * @param string $userGid The unique global ID of the user. Can also be the string "me"
     *                        to refer to the current authenticated user.
     *                        Example: "12345"
     * @param string $organizationGid The unique global ID of the organization to filter teams by.
     *                                Example: "67890"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of teams to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,description,organization,permalink_url")
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
     *               - body: Decoded response body containing team data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of teams with fields including:
     *                 - gid: Unique identifier of the team
     *                 - resource_type: Always "team"
     *                 - name: Name of the team
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GIDs provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTeamsForUser(
        string $userGid,
        string $organizationGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($userGid, 'User GID');
        $this->validateGid($organizationGid, 'Organization GID');

        $options['organization'] = $organizationGid;

        return $this->client->request('GET', "users/$userGid/teams", ['query' => $options], $responseType);
    }

    /**
     * Get teams in a workspace
     *
     * GET /workspaces/{workspace_gid}/teams
     *
     * Returns the compact records for all teams in the specified workspace.
     *
     * API Documentation: https://developers.asana.com/reference/getteamsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get teams for.
     *                             This identifier can be found in the workspace URL or
     *                             returned from workspace-related API endpoints.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of teams to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,description,organization,permalink_url")
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
     *               - body: Decoded response body containing team data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of teams with fields including:
     *                 - gid: Unique identifier of the team
     *                 - resource_type: Always "team"
     *                 - name: Name of the team
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTeamsForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        return $this->client->request('GET', "workspaces/$workspaceGid/teams", ['query' => $options], $responseType);
    }

    /**
     * Create a team
     *
     * POST /teams
     *
     * Creates a new team in the specified organization. Returns the full record of the
     * newly created team.
     *
     * API Documentation: https://developers.asana.com/reference/createteam
     *
     * @param array $data Data for creating the team. Supported fields include:
     *                    Required:
     *                    - name (string): Name of the team.
     *                      Example: "Engineering"
     *                    - organization (string): GID of the organization to create the team in.
     *                      Example: "12345"
     *                    Optional:
     *                    - description (string): Description of the team.
     *                      Example: "The engineering team builds our product."
     *                    - html_description (string): HTML formatted description of the team.
     *                    - visibility (string): The visibility of the team. One of: "secret", "request_to_join"
     *                      Example: "secret"
     *                    Example: ["name" => "Engineering", "organization" => "12345"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,description,organization,permalink_url")
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
     *               - body: Decoded response body containing created team data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created team details including:
     *                 - gid: Unique identifier of the created team
     *                 - resource_type: Always "team"
     *                 - name: Name of the team
     *                 - description: Description of the team
     *                 - organization: Object containing the organization details
     *                 - permalink_url: URL to the team in Asana
     *                 Additional fields as specified in opt_fields
     *
     * @throws InvalidArgumentException If required fields (name, organization) are missing
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function createTeam(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['name', 'organization'], 'team creation');

        return $this->client->request(
            'POST',
            'teams',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Update a team
     *
     * PUT /teams/{team_gid}
     *
     * Updates the properties of a team. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updateteam
     *
     * @param string $teamGid The unique global ID of the team to update.
     *                        This identifier can be found in the team URL or
     *                        returned from team-related API endpoints.
     *                        Example: "12345"
     * @param array $data The properties of the team to update. Can include:
     *                    - name (string): Name of the team.
     *                      Example: "Updated Team Name"
     *                    - description (string): Description of the team.
     *                    - html_description (string): HTML formatted description of the team.
     *                    - visibility (string): The visibility of the team. One of: "secret", "request_to_join"
     *                    Example: ["name" => "New Team Name", "description" => "Updated description"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,description,organization,permalink_url")
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
     *               - body: Decoded response body containing updated team data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated team details including:
     *                 - gid: Unique identifier of the team
     *                 - resource_type: Always "team"
     *                 - name: Updated name of the team
     *                 - description: Updated description of the team
     *                 - organization: Object containing the organization details
     *                 - permalink_url: URL to the team in Asana
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid team GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateTeam(
        string $teamGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');

        return $this->client->request(
            'PUT',
            "teams/$teamGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add a user to a team
     *
     * POST /teams/{team_gid}/addUser
     *
     * Adds a user to a team. The user making this call must be a member of the team
     * in order to add others. The user being added must exist in the same organization
     * as the team.
     *
     * API Documentation: https://developers.asana.com/reference/addusertoteam
     *
     * @param string $teamGid The unique global ID of the team to add the user to.
     *                        Example: "12345"
     * @param array $data Data for adding the user. Supported fields include:
     *                    Required:
     *                    - user (string): The GID of the user to add to the team.
     *                      Example: "67890"
     *                    Example: ["user" => "67890"]
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
     *               - body: Decoded response body containing the user membership data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the team membership details
     *
     * @throws InvalidArgumentException If the team GID is invalid or user field is missing
     * @throws AsanaApiException If the user doesn't exist, insufficient permissions,
     *                          or network issues occur
     */
    public function addUserToTeam(
        string $teamGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');
        $this->validateRequiredFields($data, ['user'], 'adding user to team');

        return $this->client->request(
            'POST',
            "teams/$teamGid/addUser",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove a user from a team
     *
     * POST /teams/{team_gid}/removeUser
     *
     * Removes a user from a team. The user making this call must be a member of the team
     * in order to remove others.
     *
     * API Documentation: https://developers.asana.com/reference/removeuserfromteam
     *
     * @param string $teamGid The unique global ID of the team to remove the user from.
     *                        Example: "12345"
     * @param array $data Data for removing the user. Supported fields include:
     *                    Required:
     *                    - user (string): The GID of the user to remove from the team.
     *                      Example: "67890"
     *                    Example: ["user" => "67890"]
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
     * @throws InvalidArgumentException If the team GID is invalid or user field is missing
     * @throws AsanaApiException If the user doesn't exist in the team, insufficient permissions,
     *                          or network issues occur
     */
    public function removeUserFromTeam(
        string $teamGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');
        $this->validateRequiredFields($data, ['user'], 'removing user from team');

        return $this->client->request(
            'POST',
            "teams/$teamGid/removeUser",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }
}
