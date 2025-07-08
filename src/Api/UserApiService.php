<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;

class UserApiService
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
     * Get multiple users
     *
     * GET /users
     *
     * Returns a list of all users in the organization or workspace accessible to the authenticated user.
     * Access to a user's full profile and other actions is determined by your access control settings.
     *
     * API Documentation: https://developers.asana.com/reference/getusers
     *
     * @param string|null $workspace The unique global ID of the workspace to get users from.
     *                    Either this or $team must have a value.
     * @param string|null $team The unique global ID of the team to get users from.
     *                     Either this or $workspace must have a value.
     * @param array $options Query parameters to filter and format results:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of users to return. Default is 20
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns prettier formatting in responses
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     * @throws InvalidArgumentException If neither workspace nor team is provided
     */
    public function getUsers(
        ?string $workspace = null,
        ?string $team = null,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        if (!$workspace && !$team) {
            throw new InvalidArgumentException('You must provide either a "workspace" or "team".');
        }

        if ($workspace) {
            $options['workspace'] = $workspace;
        }
        if ($team) {
            $options['team'] = $team;
        }

        return $this->client->request('GET', 'users', ['query' => $options], $responseType);
    }

    /**
     * Get a user
     *
     * GET /users/{user_gid}
     *
     * Returns the full user record for a single user identified by their GID.
     * Access to a user's full profile and other actions is determined by your access control settings.
     *
     * API Documentation: https://developers.asana.com/reference/getuser
     *
     * @param string $userGid The unique global ID of the user to retrieve. This identifier
     *                        can be found in the user URL or returned from user-related API endpoints.
     *                        Example: "12345" or "me" for the current user
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     */
    public function getUser(string $userGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "users/$userGid", ['query' => $options], $responseType);
    }

    /**
     * Get a user's favorites
     *
     * GET /users/{user_gid}/favorites
     *
     * Returns all of a user's favorites in the order they appear in Asana's sidebar.
     * Results are paginated and include projects, tasks, tags, users, portfolios, and goals.
     *
     * API Documentation: https://developers.asana.com/reference/getfavoritesforuser
     *
     * @param string $userGid The unique global ID of the user to retrieve favorites for.
     *                        This identifier can be found in the user URL or returned from user-related API endpoints.
     *                        Use "me" to refer to the current user.
     *                        Example: "12345" or "me"
     * @param array $options Parameters to customize the request:
     *                      Required:
     *                      - workspace (string): The workspace in which to get favorites
     *                      - resource_type (string): The resource type of favorites to retrieve.
     *                        Possible values: project, task, tag, user, portfolio, project_template
     *                      Optional:
     *                      - limit (int): Results to return per page. Default: 20, Maximum: 100
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     */
    public function getUserFavorites(string $userGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "users/$userGid/favorites", ['query' => $options], $responseType);
    }

    /**
     * Get users in a team
     *
     * GET /teams/{team_gid}/users
     *
     * Returns the users that are members of the team specified.
     * Team members can view and interact with each other in the team.
     *
     * API Documentation: https://developers.asana.com/reference/getusersforteam
     *
     * @param string $teamGid The unique global ID of the team to get users from.
     *                       This identifier can be found in the team URL or returned from
     *                       team-related API endpoints.
     *                       Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - offset (string): Pagination offset token
     *                      - limit (int): Maximum number of users to return. Default: 20, Maximum: 100
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     */
    public function getUsersForTeam(string $teamGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "teams/$teamGid/users", ['query' => $options], $responseType);
    }

    /**
     * Get users in a workspace or organization
     *
     * GET /workspaces/{workspace_gid}/users
     *
     * Returns the users that are members of a workspace or organization.
     * Each user's ability to interact with tasks and other resources depends on their role
     * and permissions within the workspace.
     *
     * API Documentation: https://developers.asana.com/reference/getusersforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace to get users from.
     *                          This identifier can be found in the workspace URL or returned from
     *                          workspace-related API endpoints.
     *                          Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - offset (string): Pagination offset token
     *                      - limit (int): Maximum number of users to return. Default: 20, Maximum: 100
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     */
    public function getUsersForWorkspace(string $workspaceGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "workspaces/$workspaceGid/users", ['query' => $options], $responseType);
    }

    /**
     * Get the current user
     *
     * GET /users/me
     *
     * Returns the full user record for the currently authenticated user.
     * A shortcut method that uses "me" as the user identifier.
     *
     * API Documentation: https://developers.asana.com/reference/getuser
     *
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     */
    public function getCurrentUser(array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->getUser('me', $options, $responseType);
    }

    /**
     * Get the current user's favorites
     *
     * GET /users/me/favorites
     *
     * Returns all favorites for the currently authenticated user.
     * A shortcut method that uses "me" as the user identifier.
     *
     * API Documentation: https://developers.asana.com/reference/getfavoritesforuser
     *
     * @param array $options Optional parameters to customize the request:
     *                      - workspace (string): The workspace in which to get favorites
     *                      - resource_type (string): The resource type of favorites to retrieve.
     *                        Possible values: project, task, tag, user, portfolio, goal
     *                      - limit (int): Results to return per page. Default: 20, Maximum: 100
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     * @param int $responseType The type of response to return:
     *                         - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     *                         - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     *                         - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws AsanaApiException If the API request fails due to authentication, validation,
     *                          network issues, or other API-related errors
     */
    public function getCurrentUserFavorites(array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->getUserFavorites('me', $options, $responseType);
    }
}
