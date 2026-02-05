<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class WebhooksApiService
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
     * Get multiple webhooks
     *
     * GET /webhooks
     *
     * Returns a list of webhooks for the given workspace. Webhooks allow an application
     * to be notified of changes in Asana. The filters on the request can be used to
     * limit the set of results returned.
     *
     * API Documentation: https://developers.asana.com/reference/getwebhooks
     *
     * @param string $workspaceGid The unique global ID of the workspace to get webhooks for.
     *                             This identifier can be found in the workspace URL or
     *                             returned from workspace-related API endpoints.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - resource (string): Filter webhooks by the resource they are watching.
     *                        Example: "67890"
     *                      - limit (int): Maximum number of webhooks to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "resource,target,active,filters")
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
     *               - body: Decoded response body containing webhook data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of webhooks with fields including:
     *                 - gid: Unique identifier of the webhook
     *                 - resource_type: Always "webhook"
     *                 - resource: Object containing the resource being watched
     *                 - target: The URL to receive webhook events
     *                 - active: Whether the webhook is active
     *                 - filters: Array of event filter objects
     *                 - created_at: Creation timestamp
     *                 - last_failure_at: Timestamp of last delivery failure
     *                 - last_failure_content: Content of last delivery failure
     *                 - last_success_at: Timestamp of last successful delivery
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getWebhooks(
        string $workspaceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        $options['workspace'] = $workspaceGid;

        return $this->client->request('GET', 'webhooks', ['query' => $options], $responseType);
    }

    /**
     * Get a webhook
     *
     * GET /webhooks/{webhook_gid}
     *
     * Returns the full record for the given webhook.
     *
     * API Documentation: https://developers.asana.com/reference/getwebhook
     *
     * @param string $webhookGid The unique global ID of the webhook to retrieve.
     *                           This identifier is returned when creating a webhook or
     *                           from the getWebhooks endpoint.
     *                           Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "resource,target,active,filters")
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
     *               - body: Decoded response body containing webhook data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the webhook details including:
     *                 - gid: Unique identifier of the webhook
     *                 - resource_type: Always "webhook"
     *                 - resource: Object containing the resource being watched
     *                 - target: The URL to receive webhook events
     *                 - active: Whether the webhook is active
     *                 - filters: Array of event filter objects
     *                 - created_at: Creation timestamp
     *                 - last_failure_at: Timestamp of last delivery failure
     *                 - last_failure_content: Content of last delivery failure
     *                 - last_success_at: Timestamp of last successful delivery
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid webhook GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getWebhook(
        string $webhookGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($webhookGid, 'Webhook GID');

        return $this->client->request('GET', "webhooks/$webhookGid", ['query' => $options], $responseType);
    }

    /**
     * Create a webhook
     *
     * POST /webhooks
     *
     * Establishing a webhook is a two-part process. First, a simple HTTP POST request
     * initiates the creation similar to creating any other resource. Next, in the middle
     * of this request, the webhook target URL will receive a test event to confirm that
     * the target can receive events. The webhook will only be created if the target URL
     * responds with a 200 OK to the handshake request.
     *
     * API Documentation: https://developers.asana.com/reference/createwebhook
     *
     * @param array $data Data for creating the webhook. Supported fields include:
     *                    Required:
     *                    - resource (string): GID of the resource to watch for changes.
     *                      Example: "12345"
     *                    - target (string): The URL to receive the HTTP POST.
     *                      Must be a valid HTTPS URL.
     *                      Example: "https://example.com/webhooks"
     *                    Optional:
     *                    - filters (array): An array of filter objects for this webhook.
     *                      Each filter has:
     *                      - resource_type (string): The type of resource to filter on (e.g., "task")
     *                      - resource_subtype (string): The subtype of resource (optional)
     *                      - action (string): The action to filter on (e.g., "changed", "added", "removed")
     *                      - fields (array): Array of field names to filter on (e.g., ["due_at", "due_on"])
     *                      Example: [["resource_type" => "task", "action" => "changed", "fields" => ["due_at"]]]
     *                    Example: ["resource" => "12345", "target" => "https://example.com/webhooks"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "resource,target,active,filters")
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
     *               - body: Decoded response body containing created webhook data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created webhook details including:
     *                 - gid: Unique identifier of the created webhook
     *                 - resource_type: Always "webhook"
     *                 - resource: Object containing the resource being watched
     *                 - target: The URL to receive webhook events
     *                 - active: Whether the webhook is active
     *                 - filters: Array of event filter objects
     *                 - created_at: Creation timestamp
     *                 Additional fields as specified in opt_fields
     *
     * @throws InvalidArgumentException If required fields (resource, target) are missing
     * @throws AsanaApiException If the target URL fails the handshake, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function createWebhook(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['resource', 'target'], 'webhook creation');

        return $this->client->request(
            'POST',
            'webhooks',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Update a webhook
     *
     * PUT /webhooks/{webhook_gid}
     *
     * Updates the properties of a webhook. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged. An application can only update webhooks that it
     * has created.
     *
     * API Documentation: https://developers.asana.com/reference/updatewebhook
     *
     * @param string $webhookGid The unique global ID of the webhook to update.
     *                           This identifier is returned when creating a webhook or
     *                           from the getWebhooks endpoint.
     *                           Example: "12345"
     * @param array $data The properties of the webhook to update. Can include:
     *                    - filters (array): An array of filter objects for this webhook.
     *                      Each filter has:
     *                      - resource_type (string): The type of resource to filter on (e.g., "task")
     *                      - resource_subtype (string): The subtype of resource (optional)
     *                      - action (string): The action to filter on (e.g., "changed", "added", "removed")
     *                      - fields (array): Array of field names to filter on (e.g., ["due_at", "due_on"])
     *                      Example: [["resource_type" => "task", "action" => "changed", "fields" => ["due_at"]]]
     *                    Example: ["filters" => [["resource_type" => "task", "action" => "changed"]]]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "resource,target,active,filters")
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
     *               - body: Decoded response body containing updated webhook data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated webhook details including:
     *                 - gid: Unique identifier of the webhook
     *                 - resource_type: Always "webhook"
     *                 - resource: Object containing the resource being watched
     *                 - target: The URL to receive webhook events
     *                 - active: Whether the webhook is active
     *                 - filters: Array of event filter objects
     *                 - created_at: Creation timestamp
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid webhook GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateWebhook(
        string $webhookGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($webhookGid, 'Webhook GID');

        return $this->client->request(
            'PUT',
            "webhooks/$webhookGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a webhook
     *
     * DELETE /webhooks/{webhook_gid}
     *
     * Deletes the specified webhook. This method is used to stop receiving events from
     * a previously established webhook. After deletion, no further events will be delivered
     * to the target URL.
     *
     * API Documentation: https://developers.asana.com/reference/deletewebhook
     *
     * @param string $webhookGid The unique global ID of the webhook to delete.
     *                           This identifier is returned when creating a webhook or
     *                           from the getWebhooks endpoint.
     *                           Example: "12345"
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
     *                          - Invalid webhook GID
     *                          - Webhook not found
     *                          - Insufficient permissions to delete the webhook
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deleteWebhook(string $webhookGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        $this->validateGid($webhookGid, 'Webhook GID');

        return $this->client->request('DELETE', "webhooks/$webhookGid", [], $responseType);
    }
}
