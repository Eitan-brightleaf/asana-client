<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class PortfoliosApiService
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
     * Get multiple portfolios
     *
     * GET /portfolios
     *
     * Returns a list of the portfolios in the given workspace that are owned by the given user.
     * Both the workspace and owner parameters are required.
     *
     * API Documentation: https://developers.asana.com/reference/getportfolios
     *
     * @param string $workspaceGid The unique global ID of the workspace to get portfolios from.
     *                             Example: "12345"
     * @param string $ownerGid The unique global ID of the user who owns the portfolios.
     *                         Can also be the string "me" to refer to the current user.
     *                         Example: "67890"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of portfolios to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner,workspace,members,color,created_at")
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
     *               - body: Decoded response body containing portfolio data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of portfolios with fields including:
     *                 - gid: Unique identifier of the portfolio
     *                 - resource_type: Always "portfolio"
     *                 - name: Name of the portfolio
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid GIDs provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getPortfolios(
        string $workspaceGid,
        string $ownerGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');
        $this->validateGid($ownerGid, 'Owner GID');

        $options['workspace'] = $workspaceGid;
        $options['owner'] = $ownerGid;

        return $this->client->request('GET', 'portfolios', ['query' => $options], $responseType);
    }

    /**
     * Get a portfolio
     *
     * GET /portfolios/{portfolio_gid}
     *
     * Returns the full record for a single portfolio.
     *
     * API Documentation: https://developers.asana.com/reference/getportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio to retrieve.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner,workspace,members,color,created_at,due_on,start_on")
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
     *               - body: Decoded response body containing portfolio data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the portfolio details including:
     *                 - gid: Unique identifier of the portfolio
     *                 - resource_type: Always "portfolio"
     *                 - name: Name of the portfolio
     *                 - owner: Object containing the owner details
     *                 - workspace: Object containing the workspace details
     *                 - members: Array of member objects
     *                 - color: Color of the portfolio
     *                 - created_at: Creation timestamp
     *                 - due_on: Due date
     *                 - start_on: Start date
     *                 - permalink_url: URL to the portfolio in Asana
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid portfolio GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getPortfolio(
        string $portfolioGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');

        return $this->client->request('GET', "portfolios/$portfolioGid", ['query' => $options], $responseType);
    }

    /**
     * Create a portfolio
     *
     * POST /portfolios
     *
     * Creates a new portfolio in the specified workspace. Returns the full record of the
     * newly created portfolio.
     *
     * API Documentation: https://developers.asana.com/reference/createportfolio
     *
     * @param array $data Data for creating the portfolio. Supported fields include:
     *                    Required:
     *                    - name (string): Name of the portfolio.
     *                      Example: "Product Launches"
     *                    - workspace (string): GID of the workspace to create the portfolio in.
     *                      Example: "12345"
     *                    Optional:
     *                    - color (string): Color of the portfolio (e.g., "light-green", "dark-blue")
     *                    - due_on (string): Due date in YYYY-MM-DD format
     *                    - start_on (string): Start date in YYYY-MM-DD format
     *                    - members (array): Array of user GIDs to add as members
     *                    - public (bool): Whether the portfolio is public to the workspace
     *                    Example: ["name" => "Product Launches", "workspace" => "12345"]
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
     *               - body: Decoded response body containing created portfolio data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created portfolio details
     *
     * @throws InvalidArgumentException If required fields (name, workspace) are missing
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function createPortfolio(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['name', 'workspace'], 'portfolio creation');

        return $this->client->request(
            'POST',
            'portfolios',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Update a portfolio
     *
     * PUT /portfolios/{portfolio_gid}
     *
     * Updates the properties of a portfolio. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     *
     * API Documentation: https://developers.asana.com/reference/updateportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio to update.
     *                             Example: "12345"
     * @param array $data The properties of the portfolio to update. Can include:
     *                    - name (string): Name of the portfolio
     *                    - color (string): Color of the portfolio
     *                    - due_on (string): Due date in YYYY-MM-DD format
     *                    - start_on (string): Start date in YYYY-MM-DD format
     *                    - public (bool): Whether the portfolio is public
     *                    Example: ["name" => "Updated Name", "color" => "light-green"]
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
     *               - body: Decoded response body containing updated portfolio data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated portfolio details
     *
     * @throws AsanaApiException If invalid portfolio GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updatePortfolio(
        string $portfolioGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');

        return $this->client->request(
            'PUT',
            "portfolios/$portfolioGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a portfolio
     *
     * DELETE /portfolios/{portfolio_gid}
     *
     * Deletes the specified portfolio. This does not delete the items (projects) in the
     * portfolio; they will still exist independently.
     *
     * API Documentation: https://developers.asana.com/reference/deleteportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio to delete.
     *                             Example: "12345"
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
     *                          - Invalid portfolio GID
     *                          - Portfolio not found
     *                          - Insufficient permissions to delete the portfolio
     *                          - Network connectivity issues
     *                          - Rate limiting
     */
    public function deletePortfolio(string $portfolioGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        $this->validateGid($portfolioGid, 'Portfolio GID');

        return $this->client->request('DELETE', "portfolios/$portfolioGid", [], $responseType);
    }

    /**
     * Get portfolio items
     *
     * GET /portfolios/{portfolio_gid}/items
     *
     * Returns the compact records for all items (projects) in the given portfolio.
     *
     * API Documentation: https://developers.asana.com/reference/getitemsforportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      Filtering parameters:
     *                      - limit (int): Maximum number of items to return. Default is 20, max is 100
     *                      - offset (string): Offset token for pagination
     *                      Display parameters:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,owner,due_on,current_status")
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
     *               - body: Decoded response body containing item data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of portfolio items
     *
     * @throws AsanaApiException If invalid portfolio GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getPortfolioItems(
        string $portfolioGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');

        return $this->client->request(
            'GET',
            "portfolios/$portfolioGid/items",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Add an item to a portfolio
     *
     * POST /portfolios/{portfolio_gid}/addItem
     *
     * Adds an item (project) to the specified portfolio.
     *
     * API Documentation: https://developers.asana.com/reference/additemforportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio.
     *                             Example: "12345"
     * @param array $data Data for adding the item. Supported fields include:
     *                    Required:
     *                    - item (string): The GID of the item (project) to add.
     *                      Example: "67890"
     *                    Optional:
     *                    - insert_before (string): GID of the item to insert before
     *                    - insert_after (string): GID of the item to insert after
     *                    Example: ["item" => "67890"]
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
     *               - Just the data object (empty JSON object {}) indicating successful addition
     *
     * @throws InvalidArgumentException If the portfolio GID is invalid or item field is missing
     * @throws AsanaApiException If the item doesn't exist, insufficient permissions,
     *                          or network issues occur
     */
    public function addItemToPortfolio(
        string $portfolioGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');
        $this->validateRequiredFields($data, ['item'], 'adding item to portfolio');

        return $this->client->request(
            'POST',
            "portfolios/$portfolioGid/addItem",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove an item from a portfolio
     *
     * POST /portfolios/{portfolio_gid}/removeItem
     *
     * Removes an item (project) from the specified portfolio.
     *
     * API Documentation: https://developers.asana.com/reference/removeitemforportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio.
     *                             Example: "12345"
     * @param array $data Data for removing the item. Supported fields include:
     *                    Required:
     *                    - item (string): The GID of the item (project) to remove.
     *                      Example: "67890"
     *                    Example: ["item" => "67890"]
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
     * @throws InvalidArgumentException If the portfolio GID is invalid or item field is missing
     * @throws AsanaApiException If the item doesn't exist in portfolio, insufficient permissions,
     *                          or network issues occur
     */
    public function removeItemFromPortfolio(
        string $portfolioGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');
        $this->validateRequiredFields($data, ['item'], 'removing item from portfolio');

        return $this->client->request(
            'POST',
            "portfolios/$portfolioGid/removeItem",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add members to a portfolio
     *
     * POST /portfolios/{portfolio_gid}/addMembers
     *
     * Adds members to the specified portfolio. Returns the updated portfolio record.
     *
     * API Documentation: https://developers.asana.com/reference/addmembersforportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio.
     *                             Example: "12345"
     * @param array $data Data for adding members. Supported fields include:
     *                    Required:
     *                    - members (string): A comma-separated string of user GIDs to add as members.
     *                      Example: "67890,11111"
     *                    Example: ["members" => "67890,11111"]
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
     *               - body: Decoded response body containing updated portfolio data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated portfolio details
     *
     * @throws InvalidArgumentException If the portfolio GID is invalid or members field is missing
     * @throws AsanaApiException If the users don't exist, insufficient permissions,
     *                          or network issues occur
     */
    public function addMembersToPortfolio(
        string $portfolioGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');
        $this->validateRequiredFields($data, ['members'], 'adding members to portfolio');

        return $this->client->request(
            'POST',
            "portfolios/$portfolioGid/addMembers",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove members from a portfolio
     *
     * POST /portfolios/{portfolio_gid}/removeMembers
     *
     * Removes members from the specified portfolio. Returns the updated portfolio record.
     *
     * API Documentation: https://developers.asana.com/reference/removemembersforportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio.
     *                             Example: "12345"
     * @param array $data Data for removing members. Supported fields include:
     *                    Required:
     *                    - members (string): A comma-separated string of user GIDs to remove.
     *                      Example: "67890,11111"
     *                    Example: ["members" => "67890,11111"]
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
     *               - body: Decoded response body containing updated portfolio data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated portfolio details
     *
     * @throws InvalidArgumentException If the portfolio GID is invalid or members field is missing
     * @throws AsanaApiException If the users don't exist in portfolio, insufficient permissions,
     *                          or network issues occur
     */
    public function removeMembersFromPortfolio(
        string $portfolioGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');
        $this->validateRequiredFields($data, ['members'], 'removing members from portfolio');

        return $this->client->request(
            'POST',
            "portfolios/$portfolioGid/removeMembers",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }
}
