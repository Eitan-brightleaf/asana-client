<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;

class CustomFieldApiService
{
    /**
     * The Asana API client instance
     *
     * Handles HTTP requests to the Asana API endpoints with proper authentication
     * and request formatting. This client manages the API connection details and
     * provides methods for making authenticated requests.
     *
     * @var AsanaApiClient An authenticated client for making Asana API requests
     */
    private AsanaApiClient $client;

    /**
     * Constructor
     *
     * Initializes the instance with the provided Asana API client. The client is
     * used to make authenticated requests to the Asana API.
     *
     * @param AsanaApiClient $client An instance of the AsanaApiClient responsible for
     *                               handling API requests and authentication.
     *
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

	/**
	 * Create a custom field
	 *
	 * POST /custom_fields
	 *
	 * Creates a new custom field in a workspace. Every custom field has a type, which determines how values
	 * of the custom field are formatted. Custom fields can be simple types like text, number, or dates, or
	 * complex types like enum, multi-enum, or people.
	 *
	 * API Documentation: https://developers.asana.com/reference/createcustomfield
	 *
	 * @param array $data The data for creating the custom field. Required fields include:
	 *                    - workspace (string): The globally unique ID of the workspace the custom field will
	 *                      be created in.
	 *                    - name (string): The name of the custom field.
	 *                    - resource_subtype (string): The type of the custom field. Must be one of: "text",
	 *                      "enum", "multi_enum", "number", "date", or "people".
	 *                    Optional fields include:
	 *                    - type (string): *Deprecated: New integrations should prefer resource_subtype*
	 *                    - enum_options (array): Array of objects with name and/or color keys. Required for
	 *                      enum/multi_enum custom fields.
	 *                    - enabled (boolean): Whether the custom field is enabled for all projects in workspace.
	 *                    - description (string): Description of the custom field.
	 *                    - precision (integer): For number custom fields, the number of decimal places to display.
	 *                    - format (string): Text formatting option for custom text fields.
	 *                    Example: ["workspace" => "12345", "name" => "Priority", "resource_subtype" => "enum",
	 *                              "enum_options" => [["name" => "High", "color" => "red"], ["name" => "Medium"]]]
	 * @param array $options Optional parameters to customize the request:
	 *                      - opt_fields (string): A comma-separated list of fields to include in the response
	 *                        (e.g., "name,created_by,workspace")
	 *                      - opt_pretty (bool): Returns formatted JSON if true
	 * @param int $responseType The type of response to return:
	 *                              - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
	 *                              - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
	 *                              - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
	 *
	 * @return array If $fullResponse is false:
	 *               The created custom field data including:
	 *               - gid: Unique identifier of the created custom field
	 *               - resource_type: Always "custom_field"
	 *               - name: Name of the custom field
	 *               - resource_subtype: The type of the custom field
	 *               Additional fields as specified in opt_fields
	 *               If $fullResponse is true:
	 *               - status: HTTP status code
	 *               - reason: Response status message
	 *               - headers: Response headers
	 *               - body: Decoded response body containing custom field data
	 *               - raw_body: Raw response body
	 *               - request: Original request details
	 *
	 * @throws AsanaApiException If the API request fails due to invalid data, insufficient permissions,
	 *                          network issues, or rate limiting
	 */
    public function createCustomField(array $data, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request(
            'POST',
            'custom_fields',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get a custom field
     *
     * GET /custom_fields/{custom_field_gid}
     *
     * Returns the complete definition of a custom field's metadata.
     *
     * API Documentation: https://developers.asana.com/reference/getcustomfield
     *
     * @param string $customFieldGid The globally unique identifier for the custom field.
     *                               Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,created_by,workspace,enum_options")
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
     *               - body: Decoded response body containing custom field data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the custom field details
     *
     * @throws AsanaApiException If invalid custom field GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getCustomField(string $customFieldGid, array $options = [], int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('GET', "custom_fields/$customFieldGid", ['query' => $options], $responseType);
    }

    /**
     * Update a custom field
     *
     * PUT /custom_fields/{custom_field_gid}
     *
     * Updates a custom field's metadata. Updates the name, format, description, or enum_options
     * of a custom field.
     *
     * API Documentation: https://developers.asana.com/reference/updatecustomfield
     *
     * @param string $customFieldGid The globally unique identifier for the custom field to update.
     *                               Example: "12345"
     * @param array $data The properties of the custom field to update. Can include:
     *                    - name (string): The name of the custom field
     *                    - description (string): Description of the custom field
     *                    - enabled (boolean): Whether the custom field is enabled
     *                    - enum_options (array): Array of objects to set as enum options.
     *                      For adding/updating/removing options, see createEnumOption,
     *                      updateEnumOption endpoints instead.
     *                    - precision (integer): For number custom fields, the number of decimal places
     *                    Example: ["name" => "New Name", "description" => "Updated description"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,created_by,workspace,enum_options")
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
     *               - body: Decoded response body containing custom field data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated custom field details
     *
     * @throws AsanaApiException If invalid custom field GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateCustomField(
        string $customFieldGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'PUT',
            "custom_fields/$customFieldGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a custom field
     *
     * DELETE /custom_fields/{custom_field_gid}
     *
     * Permanently deletes a custom field. Note that the field is permanently deleted and cannot be recovered.
     * This operation is only possible for users with organization admin permissions or for the person who created
     * the custom field.
     *
     * API Documentation: https://developers.asana.com/reference/deletecustomfield
     *
     * @param string $customFieldGid The globally unique identifier for the custom field to delete.
     *                               Example: "12345"
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
     *               - Complete decoded JSON response
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object (empty JSON object {})
     *
     * @throws AsanaApiException If the API request fails due to invalid custom field GID, insufficient permissions,
     *                          network connectivity issues, or rate limiting
     */
    public function deleteCustomField(string $customFieldGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request('DELETE', "custom_fields/$customFieldGid", [], $responseType);
    }

    /**
     * Get a workspace's custom fields
     *
     * GET /workspaces/{workspace_gid}/custom_fields
     *
     * Returns a list of the compact representation of all custom fields in a workspace.
     *
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsforworkspace
     *
     * @param string $workspaceGid The globally unique identifier for the workspace.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,created_by,resource_subtype")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
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
     *               - body: Decoded response body containing custom field list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of custom fields
     *
     * @throws AsanaApiException If invalid workspace GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getCustomFieldsForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/custom_fields",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Create an enum option
     *
     * POST /custom_fields/{custom_field_gid}/enum_options
     *
     * Creates an enum option and adds it to the enum custom field. Note that this method only works for custom fields
     * of type 'enum' or 'multi_enum'. This is also the preferred way to add options to an enum custom field
     * instead of using updateCustomField.
     *
     * API Documentation: https://developers.asana.com/reference/createenumoptionforcustomfield
     *
     * @param string $customFieldGid The globally unique identifier for the custom field.
     *                               Example: "12345"
     * @param array $data Data for creating the enum option:
     *                    - name (string): The name of the enum option
     *                    - color (string, optional): The color of the enum option (e.g., "blue", "red", "yellow")
     *                    - enabled (boolean, optional): Whether this enum option is selectable.
     *                    - insert_before (string, optional): GID of an option to insert before
     *                    - insert_after (string, optional): GID of an option to insert after
     *                    Example: ["name" => "Critical", "color" => "red"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,color,enabled")
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
     *               - body: Decoded response body containing enum option data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the created enum option
     *
     * @throws AsanaApiException If invalid custom field GID provided, malformed data,
     *                          custom field is not of type enum/multi_enum, insufficient permissions,
     *                          or network issues occur
     */
    public function createEnumOption(
        string $customFieldGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            "custom_fields/$customFieldGid/enum_options",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Reorder a custom field's enum
     *
     * POST /custom_fields/{custom_field_gid}/enum_options/insert
     *
     * Moves a particular enum option to be either before or after another specified enum option
     * in the custom field. Reordering enum options is only possible for custom fields of type 'enum'.
     *
     * API Documentation: https://developers.asana.com/reference/insertenumoptionforcustomfield
     *
     * @param string $customFieldGid The globally unique identifier for the custom field.
     *                               Example: "12345"
     * @param array $data Data for reordering the enum option:
     *                    - enum_option (string): The GID of the enum option to reorder
     *                    - before_enum_option (string, optional): GID of the enum option to place this one before
     *                    - after_enum_option (string, optional): GID of the enum option to place this one after
     *                    Note: You must specify exactly one of before_enum_option or after_enum_option
     *                    Example: ["enum_option" => "123", "before_enum_option" => "456"]
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
     *               - body: Decoded response body containing the updated custom field data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated custom field with reordered enum options
     *
     * @throws AsanaApiException If invalid custom field GID provided, malformed data,
     *                          custom field is not of type enum, insufficient permissions,
     *                          or network issues occur
     */
    public function reorderEnumOption(string $customFieldGid, array $data, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        return $this->client->request(
            'POST',
            "custom_fields/$customFieldGid/enum_options/insert",
            ['json' => ['data' => $data]],
            $responseType
        );
    }

    /**
     * Update an enum option
     *
     * PUT /custom_fields/{custom_field_gid}/enum_options/{enum_option_gid}
     *
     * Updates an existing enum option. Enum custom fields require at least one enabled option.
     *
     * API Documentation: https://developers.asana.com/reference/updateenumoption
     *
     * @param string $customFieldGid The globally unique identifier for the custom field.
     *                               Example: "12345"
     * @param string $enumOptionGid The globally unique identifier for the enum option.
     *                              Example: "67890"
     * @param array $data Data for updating the enum option:
     *                    - name (string, optional): The name of the enum option
     *                    - color (string, optional): The color of the enum option (e.g., "blue", "red", "yellow")
     *                    - enabled (boolean, optional): Whether the enum option is a selectable value
     *                    Example: ["name" => "Updated Name", "color" => "green"]
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "name,color,enabled")
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
     *               - body: Decoded response body containing updated enum option data
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data object and other metadata
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data object containing the updated enum option
     *
     * @throws AsanaApiException If invalid custom field GID or enum option GID provided, malformed data,
     *                          custom field is not of type enum/multi_enum, insufficient permissions,
     *                          or network issues occur
     */
    public function updateEnumOption(
        string $customFieldGid,
        string $enumOptionGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'PUT',
            "custom_fields/$customFieldGid/enum_options/$enumOptionGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get a project's custom fields
     *
     * GET /projects/{project_gid}/custom_field_settings
     *
     * Returns a list of all the custom fields settings on a project, in compact form.
     * Note that, as in other endpoints, custom field settings are distinct from the custom fields
     * themselves. Custom field settings represent a mapping of a custom field to a particular container
     * that the field can be associated with (in this case, a project).
     *
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforproject
     *
     * @param string $projectGid The globally unique identifier for the project.
     *                           Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "custom_field.name,custom_field.resource_subtype,is_important")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
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
     *               - body: Decoded response body containing custom field settings list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of custom field settings
     *
     * @throws AsanaApiException If invalid project GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getCustomFieldSettingsForProject(
        string $projectGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'GET',
            "projects/$projectGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get a portfolio's custom fields
     *
     * GET /portfolios/{portfolio_gid}/custom_field_settings
     *
     * Returns a list of all the custom fields settings on a portfolio, in compact form.
     * Note that, as in other endpoints, custom field settings are distinct from the custom fields
     * themselves. Custom field settings represent a mapping of a custom field to a particular container
     * that the field can be associated with (in this case, a portfolio).
     *
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforportfolio
     *
     * @param string $portfolioGid The globally unique identifier for the portfolio.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *                      - opt_fields (string): A comma-separated list of fields to include in the response
     *                        (e.g., "custom_field.name,custom_field.resource_subtype,is_important")
     *                      - opt_pretty (bool): Returns formatted JSON if true
     *                      - limit (int): Results to return per page (1-100)
     *                      - offset (string): Pagination offset token
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
     *               - body: Decoded response body containing custom field settings list
     *               - raw_body: Raw response body
     *               - request: Original request details
     *               If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     *               - Complete decoded JSON response including data array and pagination info
     *               If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     *               - Just the data array containing the list of custom field settings
     *
     * @throws AsanaApiException If invalid portfolio GID provided, permission errors,
     *                          network issues, or rate limiting occurs
     */
    public function getCustomFieldSettingsForPortfolio(
        string $portfolioGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'GET',
            "portfolios/$portfolioGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }
}
