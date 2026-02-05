<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class EventsApiService
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
     * Get events on a resource
     *
     * GET /events
     *
     * Returns the set of events that have occurred since the last request. The Events API
     * uses a sync token-based mechanism to provide incremental updates. On the first call,
     * provide only the resource GID; the response will include the initial set of events
     * and a sync token. On subsequent calls, include the sync token to receive only new
     * events since the last request.
     *
     * If the sync token is too old (expired), the API will return an error with a new
     * sync token that can be used to get future events.
     *
     * API Documentation: https://developers.asana.com/reference/getevents
     *
     * @param string $resourceGid The unique global ID of the resource to get events for.
     *                            This can be a task, project, or other Asana resource.
     *                            Example: "12345"
     * @param string|null $syncToken A sync token received from a previous getEvents call.
     *                               Pass null on the first call to establish the initial
     *                               sync position. The API will return a new sync token
     *                               with each response.
     *                               Example: "de4774f6915eae04714ca93bb2f5ee81"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "user,resource,type,action,parent,created_at")
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
     *               - body: Decoded response body containing event data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including:
     *                 - data: Array of event objects
     *                 - sync: The new sync token to use for the next request
     *                 - has_more: Whether there are more events to fetch
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of events with fields including:
     *                 - user: The user who triggered the event
     *                 - resource: The resource that was affected
     *                 - type: The type of event (e.g., "task", "project", "story")
     *                 - action: The action that occurred (e.g., "changed", "added", "removed")
     *                 - parent: The parent resource if applicable
     *                 - created_at: Timestamp when the event occurred
     *                 - change: Object describing what changed (field, action, new_value, etc.)
     *                 Additional fields as specified in opt_fields
     *
     * @throws InvalidArgumentException If the resource GID is empty or not numeric
     * @throws AsanaApiException If the sync token is invalid/expired (412 Precondition Failed),
     *                          insufficient permissions, network issues, or rate limiting occurs
     */
    public function getEvents(
        string $resourceGid,
        ?string $syncToken = null,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($resourceGid, 'Resource GID');

        $options['resource'] = $resourceGid;

        if ($syncToken !== null) {
            $options['sync'] = $syncToken;
        }

        return $this->client->request('GET', 'events', ['query' => $options], $responseType);
    }
}
