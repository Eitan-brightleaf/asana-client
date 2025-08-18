<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

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
     * GET /memberships
     *
     * Returns the compact membership records for the memberships matching the given filters.
     * Memberships represent connections between non-project objects and relevant users,
     * indicating a user's access and permissions in relation to that object.
     *
     * API Documentation: https://developers.asana.com/reference/getmemberships
     *
     * @param array $options Query parameters to filter and format results:
     *                      Required filtering parameters (at least one of):
     *                      - parent (string): A resource ID to filter memberships by parent
     *                        (project, goal, portfolio, or custom_field). Example: "12345"
     *                      - portfolio (string): A portfolio ID to filter memberships by portfolio.
     *                        Example: "67890"
     *                      Optional filtering parameters:
     *                      - member (string): A team or user ID to filter memberships by member.
     *                        Example: "11111"
     *                      - limit (int): Maximum number of items to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "gid,resource_type,access_level,member,parent")
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
     *               - body: Decoded response body containing membership list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of memberships with fields including:
     *                 - gid: Unique identifier of the membership
     *                 - resource_type: Always "membership"
     *                 - access_level: The access level of the membership
     *                 - member: Object containing member details (user or team)
     *                 - parent: Object containing parent resource details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Missing required parameters
     *                          - Invalid parameter values
     *                          - Insufficient permissions
     *                          - Rate limiting
     *                          - Network connectivity issues
     */
    public function getMemberships(array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', 'memberships', ['query' => $options], $responseType);
    }

    /**
     * Create a membership
     *
     * POST /memberships
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
     *                      Example: "12345"
     *                    - member (string): The gid of the user or team being added as a member.
     *                      Example: "67890"
     *                    Optional:
     *                    - access_level (string): Sets the access level for the member.
     *                      Goals can have access levels: "editor", "commenter"
     *                      Projects can have access levels: "admin", "editor", "commenter"
     *                      Portfolios can have access levels: "admin", "editor", "viewer"
     *                      Custom Fields can have access levels: "admin", "editor", "user"
     *                    Example: ["parent" => "12345", "member" => "67890", "access_level" => "editor"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "gid,resource_type,access_level,member,parent")
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
     *               - body: Decoded response body containing created membership data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created membership details including:
     *                 - gid: Unique identifier of the created membership
     *                 - resource_type: Always "membership"
     *                 - access_level: The access level assigned to the membership
     *                 - member: Object containing member details (user or team)
     *                 - parent: Object containing parent resource details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Missing required fields
     *                          - Invalid field values
     *                          - Insufficient permissions
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function createMembership(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            'memberships',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get a membership
     *
     * GET /memberships/{membership_gid}
     *
     * Returns the complete membership record for a single membership.
     *
     * API Documentation: https://developers.asana.com/reference/getmembership
     *
     * @param string $membershipGid The unique global ID of the membership to retrieve.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     *                              Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "gid,resource_type,access_level,member,parent")
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
     *               - body: Decoded response body containing membership data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the membership details including:
     *                 - gid: Unique identifier of the membership
     *                 - resource_type: Always "membership"
     *                 - access_level: The access level of the membership
     *                 - member: Object containing member details (user or team)
     *                 - parent: Object containing parent resource details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid membership GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getMembership(
        string $membershipGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', "memberships/$membershipGid", ['query' => $options], $responseType);
    }

    /**
     * Update a membership
     *
     * PUT /memberships/{membership_gid}
     *
     * Updates the properties of a membership. Only the fields provided in the data block
     * will be updated; any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatemembership
     *
     * @param string $membershipGid The unique global ID of the membership to update.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     *                              Example: "12345"
     * @param array $data The properties of the membership to update. Can include:
     *                    - access_level (string): The updated access level for the membership.
     *                      Allowed values depend on the parent type:
     *                      Goals: "editor", "commenter"
     *                      Projects: "admin", "editor", "commenter"
     *                      Portfolios: "admin", "editor", "viewer"
     *                      Custom Fields: "admin", "editor", "user"
     *                    Example: ["access_level" => "editor"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "gid,resource_type,access_level,member,parent")
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
     *               - body: Decoded response body containing updated membership data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated membership details including:
     *                 - gid: Unique identifier of the membership
     *                 - resource_type: Always "membership"
     *                 - access_level: The updated access level of the membership
     *                 - member: Object containing member details (user or team)
     *                 - parent: Object containing parent resource details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid membership GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateMembership(
        string $membershipGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'PUT',
            "memberships/$membershipGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a membership
     *
     * DELETE /memberships/{membership_gid}
     *
     * Deletes a membership. This is the way to remove a user or team from a
     * portfolio, project, goal, or custom_field.
     *
     * API Documentation: https://developers.asana.com/reference/deletemembership
     *
     * @param string $membershipGid The unique global ID of the membership to delete.
     *                              This identifier can be found in the membership URL or
     *                              returned from membership-related API endpoints.
     *                              Example: "12345"
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
     *                          - Invalid membership GID
     *                          - Insufficient permissions to delete the membership
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteMembership(string $membershipGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('DELETE', "memberships/$membershipGid", [], $responseType);
    }
}
