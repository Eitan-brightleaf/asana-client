<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class TimeTrackingEntriesApiService
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
     * Get a time tracking entry
     *
     * GET /time_tracking_entries/{time_tracking_entry_gid}
     *
     * Returns the full record for a single time tracking entry.
     *
     * API Documentation: https://developers.asana.com/reference/gettimetrackingentry
     *
     * @param string $timeTrackingEntryGid The unique global ID of the time tracking entry.
     *                                     Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "created_by,duration_minutes,entered_on,task")
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
     *               - body: Decoded response body containing time tracking entry data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the entry details including:
     *                 - gid: Unique identifier of the time tracking entry
     *                 - resource_type: Always "time_tracking_entry"
     *                 - duration_minutes: Duration in minutes
     *                 - entered_on: Date the time was entered for
     *                 - created_by: Object containing the creator details
     *                 - task: Object containing the associated task details
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTimeTrackingEntry(
        string $timeTrackingEntryGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingEntryGid, 'Time Tracking Entry GID');

        return $this->client->request(
            'GET',
            "time_tracking_entries/$timeTrackingEntryGid",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get time tracking entries for a task
     *
     * GET /tasks/{task_gid}/time_tracking_entries
     *
     * Returns compact time tracking entry records for a given task.
     *
     * API Documentation: https://developers.asana.com/reference/gettimetrackingentriesfortask
     *
     * @param string $taskGid The unique global ID of the task.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Pagination parameters:
     *                      - limit (int): Maximum number of entries to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include
     *                        (e.g., "created_by,duration_minutes,entered_on")
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
     *               - body: Decoded response body containing time tracking entry data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of time tracking entries
     *
     * @throws AsanaApiException If invalid task GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getTimeTrackingEntriesForTask(
        string $taskGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($taskGid, 'Task GID');

        return $this->client->request(
            'GET',
            "tasks/$taskGid/time_tracking_entries",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Create a time tracking entry on a task
     *
     * POST /tasks/{task_gid}/time_tracking_entries
     *
     * Creates a time tracking entry on the given task. Returns the full record of the
     * newly created time tracking entry.
     *
     * API Documentation: https://developers.asana.com/reference/createtimetrackingentry
     *
     * @param string $taskGid The unique global ID of the task.
     *                        Example: "12345"
     * @param array $data Data for creating the time tracking entry. Supported fields include:
     *                    Required:
     *                    - entered_on (string): Date the time entry is for, in YYYY-MM-DD format.
     *                      Example: "2026-02-05"
     *                    - duration_minutes (int): Duration of the time entry in minutes.
     *                      Example: 60
     *                    Optional:
     *                    - created_by (string): GID of the user who created the entry.
     *                      Defaults to the authenticated user.
     *                    Example: ["entered_on" => "2026-02-05", "duration_minutes" => 60]
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
     *               - body: Decoded response body containing created entry data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created entry details
     *
     * @throws InvalidArgumentException If required fields (entered_on, duration_minutes) are missing
     *                                  or if the task GID is invalid
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function createTimeTrackingEntry(
        string $taskGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($taskGid, 'Task GID');
        $this->validateRequiredFields(
            $data,
            ['entered_on', 'duration_minutes'],
            'time tracking entry creation'
        );

        return $this->client->request(
            'POST',
            "tasks/$taskGid/time_tracking_entries",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Update a time tracking entry
     *
     * PUT /time_tracking_entries/{time_tracking_entry_gid}
     *
     * Updates the properties of a time tracking entry. Only the fields provided in the data
     * block will be updated; any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updatetimetrackingentry
     *
     * @param string $timeTrackingEntryGid The unique global ID of the time tracking entry.
     *                                     Example: "12345"
     * @param array $data The properties to update. Can include:
     *                    - duration_minutes (int): Duration in minutes
     *                    - entered_on (string): Date in YYYY-MM-DD format
     *                    Example: ["duration_minutes" => 90]
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
     *               - body: Decoded response body containing updated entry data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated entry details
     *
     * @throws AsanaApiException If invalid GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateTimeTrackingEntry(
        string $timeTrackingEntryGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingEntryGid, 'Time Tracking Entry GID');

        return $this->client->request(
            'PUT',
            "time_tracking_entries/$timeTrackingEntryGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a time tracking entry
     *
     * DELETE /time_tracking_entries/{time_tracking_entry_gid}
     *
     * Deletes the specified time tracking entry. This action is permanent and cannot be undone.
     *
     * API Documentation: https://developers.asana.com/reference/deletetimetrackingentry
     *
     * @param string $timeTrackingEntryGid The unique global ID of the time tracking entry.
     *                                     Example: "12345"
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
     *                          - Invalid time tracking entry GID
     *                          - Entry not found
     *                          - Insufficient permissions to delete the entry
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteTimeTrackingEntry(
        string $timeTrackingEntryGid,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingEntryGid, 'Time Tracking Entry GID');

        return $this->client->request(
            'DELETE',
            "time_tracking_entries/$timeTrackingEntryGid",
            [],
            $responseType
        );
    }
}
