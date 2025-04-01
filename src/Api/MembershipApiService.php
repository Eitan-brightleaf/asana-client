<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Http\AsanaApiClient;
use GuzzleHttp\Exception\RequestException;

class MembershipApiService
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
     * Get multiple memberships
     *
     * Returns the compact membership records for the memberships matching the given filters.
     * Memberships represent connections between non-project objects and relevant users, 
     * indicating a user's access and permissions in relation to that object.
     *
     * API Documentation: https://developers.asana.com/reference/getmemberships
     *
     * @param array $options Query parameters to filter and format results:
     *                      Required filtering parameters (at least one of):
     *                      - parent (string): A resource ID to filter memberships by parent (project, goal, portfolio, or custom_field)
     *                      - portfolio (string): A portfolio ID to filter memberships by portfolio
     *                      Optional filtering parameters:
     *                      - member (string): A team or user ID to filter memberships by member
     *                      - limit (int): Maximum number of items to return. Default is 20
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns prettier formatting in responses
     *
     * @return array List of memberships matching the filters. Each membership contains:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               Additional fields if specified in opt_fields
     *
     * @throws RequestException If the API request fails due to:
     *                         - Missing required parameters
     *                         - Invalid parameter values
     *                         - Insufficient permissions
     *                         - Rate limiting
     *                         - Network connectivity issues
     */
    public function getMemberships(array $options = []): array
    {
        return $this->client->request('GET', 'memberships', ['query' => $options]);
    }

    /**
     * Create a membership
     *
     * Creates a new membership in a parent object (goal, project, or portfolio).
     * Memberships provide a way to add users as members of top-level objects.
     * Portfolios and custom fields only support users as members.
     *
     * API Documentation: https://developers.asana.com/reference/createmembership
     *
     * @param array $data Data for creating the membership. Supported fields include:
     *                    Required:
     *                    - parent (string): The parent id of the membership (goal, project, portfolio, or custom_field)
     *                    - member (string): The gid of the user or team being added as a member
     *                    Optional:
     *                    - access_level (string): Sets the access level for the member. Goals can have access levels 'editor' or 'commenter'. Projects can have
     *                      access levels 'admin', 'editor' or 'commenter'. Portfolios can have access levels 'admin', 'editor' or 'viewer'. Custom Fields can
     *                      have access levels 'admin', 'editor' or 'user'.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *
     * @return array The created membership data including at minimum:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If the API request fails due to:
     *                         - Missing required fields
     *                         - Invalid field values
     *                         - Insufficient permissions
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
    public function createMembership(array $data, array $options = []): array
    {
        return $this->client->request('POST', 'memberships', ['json' => $data, 'query' => $options]);
    }

    /**
     * Get a membership
     *
     * Returns the complete membership record for a single membership.
     *
     * API Documentation: https://developers.asana.com/reference/getmembership
     *
     * @param string $membershipGid The unique global ID of the membership to retrieve.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *
     * @return array Membership record containing at minimum:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               - parent: The parent object (portfolio, project, goal, or custom_field) of this membership
     *               - member: The member (user or team) in this membership
     *               - access_level: The access level of the membership (admin, editor, commenter, viewer, etc.)
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid membership GID provided, insufficient permissions,
     *                         network issues, or rate limiting occurs
     */
    public function getMembership(string $membershipGid, array $options = []): array
    {
        return $this->client->request('GET', "memberships/$membershipGid", ['query' => $options]);
    }

    /**
     * Update a membership
     *
     * Updates the properties of a membership. Only the fields provided in the data block
     * will be updated; any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatemembership
     *
     * @param string $membershipGid The unique global ID of the membership to update.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     * @param array $data The properties of the membership to update. Can include:
     *                    - access_level (string): The updated access level for the membership.
     *                      Allowed values depend on the parent type:
     *                      - Goals: 'editor', 'commenter'
     *                      - Projects: 'admin', 'editor', 'commenter'
     *                      - Portfolios: 'admin', 'editor', 'viewer'
     *                      - Custom Fields: 'admin', 'editor', 'user'
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *
     * @return array The updated membership data including:
     *               - gid: Membership's unique identifier
     *               - resource_type: Always "membership"
     *               - parent: The parent object (portfolio, project, goal, or custom_field) of this membership
     *               - member: The member (user or team) in this membership
     *               - access_level: The updated access level of the membership
     *               Additional fields as specified in opt_fields
     *
     * @throws RequestException If invalid membership GID provided, malformed data,
     *                         insufficient permissions, or network issues occur
     */
    public function updateMembership(string $membershipGid, array $data, array $options = []): array
    {
        return $this->client->request('PUT', "memberships/$membershipGid", ['json' => $data, 'query' => $options]);
    }

    /**
     * Delete a membership
     *
     * Deletes a membership. This is the way to remove a user or team from a
     * portfolio, project, goal, or custom_field.
     *
     * API Documentation: https://developers.asana.com/reference/deletemembership
     *
     * @param string $membershipGid The unique global ID of the membership to delete.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     *
     * @return array Empty data object containing only the HTTP status indicator:
     *               - data: An empty JSON object {}
     *
     * @throws RequestException If the API request fails due to:
     *                         - Invalid membership GID
     *                         - Insufficient permissions to delete the membership
     *                         - Network connectivity issues
     *                         - Rate limiting
     */
    public function deleteMembership(string $membershipGid): array
    {
        return $this->client->request('DELETE', "memberships/$membershipGid");
    }
}