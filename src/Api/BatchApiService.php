<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class BatchApiService
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
     * Submit a batch request
     *
     * POST /batch
     *
     * Submits multiple API requests in a single HTTP call. Each action in the batch
     * represents an individual API request. The batch API returns a response for each
     * action in the same order they were submitted.
     *
     * API Documentation: https://developers.asana.com/reference/createbatchrequest
     *
     * @param array $actions An array of actions to execute in the batch. Each action is
     *                       an associative array with the following fields:
     *                       Required:
     *                       - relative_path (string): The API endpoint path (e.g., "/tasks/12345").
     *                       - method (string): The HTTP method (get, post, put, delete).
     *                       Optional:
     *                       - data (array): The request body for post/put requests.
     *                       - options (array): Query parameters for the request.
     *                         - fields (array): Array of fields to include in the response.
     *                         - limit (int): Maximum number of items to return.
     *                         - offset (string): Offset token for pagination.
     *                       Example:
     *                       [
     *                           [
     *                               "relative_path" => "/tasks/12345",
     *                               "method" => "get",
     *                           ],
     *                           [
     *                               "relative_path" => "/tasks",
     *                               "method" => "post",
     *                               "data" => ["name" => "New Task", "workspace" => "67890"],
     *                           ],
     *                       ]
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
     *               - body: Decoded response body containing batch results
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the batch results, where each result has:
     *                 - status_code: HTTP status code for the individual request
     *                 - headers: Response headers for the individual request
     *                 - body: Decoded response body for the individual request
     *
     * @throws InvalidArgumentException If actions array is empty or actions are missing
     *                                  required fields (relative_path, method)
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function submitBatchRequest(
        array $actions,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateActions($actions);

        return $this->client->request(
            'POST',
            'batch',
            ['json' => ['data' => ['actions' => $actions]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Validate the batch actions array.
     *
     * Ensures the actions array is non-empty and each action contains
     * the required fields: relative_path and method.
     *
     * @param array $actions The actions array to validate.
     *
     * @throws InvalidArgumentException If actions is empty or any action is missing required fields.
     */
    private function validateActions(array $actions): void
    {
        if (empty($actions)) {
            throw new InvalidArgumentException(
                'Actions array must not be empty for batch request.'
            );
        }

        foreach ($actions as $index => $action) {
            if (!is_array($action)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Each action must be an array, invalid action at index %d.',
                        $index
                    )
                );
            }

            $missingFields = [];

            if (
                !isset($action['relative_path']) || !is_string($action['relative_path'])
                || trim($action['relative_path']) === ''
            ) {
                $missingFields[] = 'relative_path';
            }

            if (
                !isset($action['method']) || !is_string($action['method'])
                || trim($action['method']) === ''
            ) {
                $missingFields[] = 'method';
            }

            if (!empty($missingFields)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Action at index %d is missing required field(s): %s',
                        $index,
                        implode(', ', $missingFields)
                    )
                );
            }
        }
    }
}
