<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Http\AsanaApiClient;
use GuzzleHttp\Exception\RequestException;

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
     *
     * @return array List of users matching the filters. Each user contains:
     *               - gid: User's unique identifier
     *               - name: User's full name
     *               - resource_type: Always "user"
     *               Additional fields if specified in opt_fields
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid parameter values
     *                         - Insufficient permissions
     *                         - Rate limiting
     *                         - Network connectivity issues
     */
    public function getUsers(?string $workspace = null, ?string $team = null, array $options = []): array
    {
        if (!$workspace && !$team) {
            throw new \InvalidArgumentException('You must provide either a "workspace" or "team".');
        }

        if ($workspace) {
            $options['workspace'] = $workspace;
        }
        if ($team) {
            $options['team'] = $team;
        }

        return $this->client->request('GET', 'users', ['query' => $options]);
    }

    /**
     * Get a user
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
     *
     * @return array User record containing at minimum:
     *               - gid: Unique user identifier
     *               - resource_type: Always "user"
     *               - name: User's full name
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid user GID provided, insufficient permissions,
     *                         network issues, or rate limiting occurs
     */
    public function getUser(string $userGid, array $options = []): array
    {
        return $this->client->request('GET', "users/$userGid", ['query' => $options]);
    }

    /**
     * Get a user's favorites
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
     *
     * @return array List of favorite resources containing at minimum:
     *               - gid: Resource identifier
     *               - resource_type: Type of resource ("task", "project", etc.)
     *               - name: Resource name
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid user GID provided, insufficient permissions,
     *                         network issues, or rate limiting occurs
     */
    public function getUserFavorites(string $userGid, array $options = []): array
    {
        return $this->client->request('GET', "users/$userGid/favorites", ['query' => $options]);
    }

    /**
     * Get users in a team
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
     *
     * @return array List of users in the team containing at minimum:
     *               - gid: User's unique identifier
     *               - name: User's full name
     *               - resource_type: Always "user"
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid team GID provided, insufficient permissions,
     *                         network issues, or rate limiting occurs
     */
    public function getUsersForTeam(string $teamGid, array $options = []): array
    {
        return $this->client->request('GET', "teams/$teamGid/users", ['query' => $options]);
    }

    /**
     * Get users in a workspace or organization
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
     *
     * @return array List of users in the workspace containing at minimum:
     *               - gid: User's unique identifier
     *               - name: User's full name
     *               - resource_type: Always "user"
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid workspace GID provided, insufficient permissions,
     *                         network issues, or rate limiting occurs
     */
    public function getUsersForWorkspace(string $workspaceGid, array $options = []): array
    {
        return $this->client->request('GET', "workspaces/$workspaceGid/users", ['query' => $options]);
    }

    /**
     * Get the current user
     *
     * Returns the full user record for the currently authenticated user.
     * A shortcut method that uses "me" as the user identifier.
     *
     * API Documentation: https://developers.asana.com/reference/getuser
     *
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *
     * @return array User record containing at minimum:
     *               - gid: Unique user identifier
     *               - resource_type: Always "user"
     *               - name: User's full name
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If authentication fails or network issues occur
     */
    public function getCurrentUser(array $options = []): array
    {
        return $this->getUser('me', $options);
    }

    /**
     * Get the current user's favorites
     *
     * Returns all favorites for the currently authenticated user.
     * A shortcut method that uses "me" as the user identifier.
     *
     * API Documentation: https://developers.asana.com/reference/getfavorites
     *
     * @param array $options Optional parameters to customize the request:
     *                      - workspace (string): The workspace in which to get favorites
     *                      - resource_type (string): The resource type of favorites to retrieve.
     *                        Possible values: project, task, tag, user, portfolio, goal
     *                      - limit (int): Results to return per page. Default: 20, Maximum: 100
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *
     * @return array List of favorite resources
     *
     * @throws RequestException If authentication fails or network issues occur
     */
    public function getCurrentUserFavorites(array $options = []): array
    {
        return $this->getUserFavorites('me', $options);
    }
}
