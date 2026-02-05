<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class StatusUpdatesApiService
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
     * Get a status update
     *
     * GET /status_updates/{status_update_gid}
     *
     * Returns the full record for a single status update.
     *
     * API Documentation: https://developers.asana.com/reference/getstatusupdate
     *
     * @param string $statusUpdateGid The unique global ID of the status update.
     *                                Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "title,text,status_type,author,created_at,parent")
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
     *               - body: Decoded response body containing status update data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the status update details including:
     *                 - gid: Unique identifier of the status update
     *                 - resource_type: Always "status_update"
     *                 - title: Title of the status update
     *                 - text: Body text of the status update
     *                 - status_type: One of "on_track", "at_risk", "off_track", etc.
     *                 - author: Object containing the author details
     *                 - parent: Object containing the parent resource details
     *                 - created_at: Creation timestamp
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getStatusUpdate(
        string $statusUpdateGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($statusUpdateGid, 'Status Update GID');

        return $this->client->request(
            'GET',
            "status_updates/$statusUpdateGid",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get status updates for an object
     *
     * GET /status_updates
     *
     * Returns the compact status update records for the given parent object
     * (project, portfolio, or goal). The parent parameter is required.
     *
     * API Documentation: https://developers.asana.com/reference/getstatusupdatesforobject
     *
     * @param string $parentGid The unique global ID of the parent resource
     *                          (project, portfolio, or goal).
     *                          Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - created_since (string): Only return status updates created since
     *                        this time (ISO 8601 datetime string)
     *                      Pagination parameters:
     *                      - limit (int): Maximum number of status updates to return.
     *                        Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "title,text,status_type,author,created_at")
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
     *               - body: Decoded response body containing status update data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of status updates
     *
     * @throws AsanaApiException If invalid parent GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getStatusUpdatesForObject(
        string $parentGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($parentGid, 'Parent GID');

        $options['parent'] = $parentGid;

        return $this->client->request(
            'GET',
            'status_updates',
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Create a status update
     *
     * POST /status_updates
     *
     * Creates a new status update on a parent object (project, portfolio, or goal).
     * Returns the full record of the newly created status update.
     *
     * API Documentation: https://developers.asana.com/reference/createstatusupdate
     *
     * @param array $data Data for creating the status update. Supported fields include:
     *                    Required:
     *                    - parent (string): GID of the parent resource (project, portfolio, or goal).
     *                      Example: "12345"
     *                    - text (string): The text content of the status update.
     *                      Example: "Project is on track for Q1 delivery."
     *                    - status_type (string): The status type. One of:
     *                      "on_track", "at_risk", "off_track", "on_hold", "complete",
     *                      "achieved", "partial", "missed", "dropped"
     *                    Optional:
     *                    - title (string): The title of the status update
     *                    Example: [
     *                        "parent" => "12345",
     *                        "text" => "Project is on track.",
     *                        "status_type" => "on_track",
     *                    ]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
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
     *               - body: Decoded response body containing created status update data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created status update details
     *
     * @throws InvalidArgumentException If required fields (parent, text, status_type) are missing
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function createStatusUpdate(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields(
            $data,
            ['parent', 'text', 'status_type'],
            'status update creation'
        );

        return $this->client->request(
            'POST',
            'status_updates',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a status update
     *
     * DELETE /status_updates/{status_update_gid}
     *
     * Deletes the specified status update. This action is permanent and cannot be undone.
     *
     * API Documentation: https://developers.asana.com/reference/deletestatusupdate
     *
     * @param string $statusUpdateGid The unique global ID of the status update to delete.
     *                                Example: "12345"
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
     *               - body: Decoded response body (empty data object)
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including empty data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {}) indicating successful deletion
     *
     * @throws AsanaApiException If the API request fails due to:
     *                          - Invalid status update GID
     *                          - Status update not found
     *                          - Insufficient permissions to delete the status update
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteStatusUpdate(
        string $statusUpdateGid,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($statusUpdateGid, 'Status Update GID');

        return $this->client->request(
            'DELETE',
            "status_updates/$statusUpdateGid",
            [],
            $responseType
        );
    }
}
