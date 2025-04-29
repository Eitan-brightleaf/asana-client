# Brightleaf Digital Asana API Client for PHP

A modern, maintained PHP client library for the Asana API.

## Motivation

This library was created because the official Asana PHP library is no longer maintained, is outdated, and uses a library with a known security vulnerability. After searching for alternatives, I couldn't find any third-party libraries that appeared to be actively maintained.

## Status

This is my first library of this kind, and I am still developing my skills as a junior developer. Any reviews, comments, contributions, or suggestions are highly welcome - especially since my only peer review so far has been from AI. I would particularly appreciate help with:

- Writing tests
- Reviewing documentation
- Identifying improvements

## OAuth Scopes

This library now supports Asana's new OAuth permission scopes. These scopes provide more granular control over what 
actions an app can perform following the principle of least privilege and should enhance user trust and so increase app 
adoption. 

### Important Notes:

- **Incomplete Rollout**: Asana has not yet introduced scopes for all API endpoints. More scopes will be added in the future and incorporated into this library.
- **Backward Compatibility**: Existing apps can continue to use the `default` scope (full access) for now, and new apps can still toggle this option on in the app settings.
- **Getting Full Access With This Library**:
  - When creating your app in Asana, request full access permissions
  - When generating an authorization URL, pass an empty array for scopes: `$authUrl = $asanaClient->getAuthorizationUrl([]);`
- **Helper `Scopes` Class**: To simplify things for developers using this library, I've created a helper `Scopes` class containing
constants with available scopes. As noted below the library may not cover all API endpoints so some scopes in helper class
may correspond to an endpoint without support. Contributions and help expanding the library to have full API coverage is
welcome!

For more information about the new OAuth scopes and implementation details, refer to [Asana's announcement](https://forum.asana.com/t/new-oauth-permission-scopes/1048556/1) and its linked documentation.

## Features

- Modern PHP implementation
- Supports both OAuth 2.0 and Personal Access Tokens (PATs)
- Easy-to-use API that follows consistent patterns
- Fixes common pain points from the official Asana library
- Actively maintained

## API Coverage

This library may not support all parts of the Asana API. I've focused primarily on the endpoints relevant to my own work, generally supporting all methods for those endpoints. Contributions to expand coverage to additional endpoints are welcome!

## Design Decisions

- When a field is required by an Asana API endpoint (such as a workspace GID), it's typically required as a method argument
- Some exceptions exist where it made more sense to let users include required fields in the data array (for example, in `createTask()` where users need to provide several fields anyway, and might use a workspace GID or project GID)
- Consistent return patterns to make working with responses predictable
- Focus on developer experience and ease of use

## Installation

```bash
composer require brightleafdigital/asana-client
```
then use Composer's autoload:
```php
require __DIR__.'/vendor/autoload.php';
```

## Basic Usage

To get started you need an Asana app configured with a proper redirect URL. You get the client ID and secret from the app. Remember to store them securely!
Please read the [official documentation](https://developers.asana.com/docs/overview#authentication-basics) if you aren't sure how to set up an app.

### Using Personal Access Token (PAT)

```php
use BrightleafDigital\AsanaClient;

$personalAccessToken = 'your-personal-access-token';
$asanaClient = AsanaClient::withPersonalAccessToken($personalAccessToken);

// Get user information
$me = $asanaClient->users()->me();

// Create a task
$taskData = [
    'name' => 'My new task',
    'notes' => 'Task description',
    'projects' => ['12345678901234'] // Project GID
];
$task = $asanaClient->tasks()->createTask($taskData);
```

### Using OAuth 2.0

```php
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Auth\Scopes;

$clientId = 'your-client-id';
$clientSecret = 'your-client-secret';
$redirectUri = 'https://your-app.com/callback';

// Create a client and get the authorization URL
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri);

// Option 1: Request specific scopes
$authUrl = $asanaClient->getAuthorizationUrl([
    Scopes::TASKS_READ,
    Scopes::PROJECTS_READ,
    Scopes::USERS_READ
]);

// Option 2: Use default/full access (pass an empty array). May not be supported after July 2025.
// $authUrl = $asanaClient->getAuthorizationUrl([]);

// Redirect the user to $authUrl
// After authorization, Asana will redirect back to your callback URL

// In your callback handler:
$code = $_GET['code'];
$tokenData = $asanaClient->handleCallback($code);

// Save $tokenData for future use
// Then use the client
$workspaces = $asanaClient->users()->getCurrentUser();
```
You will retrieve an access token that contains the token itself, which expires in an hour, the timestamp of expiry, 
and a refresh token you can use to get a new access token.

While the Asana client has a `refreshToken()` method you can use, the library is supposed to take care of that automatically,
leaving you free to work on what you really need to. Built into the library is a quick check before any api calls to 
make sure the token is not expired, and if it is to refresh it.

More examples are available in the `examples` folder, including:
- OAuth flow setup with PKCE and state validation
- OAuth flow without additional security measures
- Using Personal Access Tokens
- Basic API usage examples
- All examples can be run directly in a browser

## Documentation Gaps

If you find something that isn't clear from either this library's documentation or the official Asana API documentation, the Asana developer forum is a valuable resource. There are often details or workarounds discussed there that aren't covered in the official documentation.

For example, creating a task in a specific section isn't documented in the API reference but can be found in forum discussions. If you discover such gaps:

1. Check the [Asana Developer Forum](https://forum.asana.com/c/developers/13)
2. Open an issue in this repository
3. Feel free to link to relevant forum or Stack Overflow posts

## Before Version 1.0.0

Before we can move to version 1.0.0, we need the following:

   **Expanded Test Coverage**
  - Add more unit and integration tests
  - Improve test documentation
  - Improve existing tests

   **Workflow Automation**
  - Implement automated testing workflows
  - Add security scanning and checks
  - Add code quality checks

   **Enhanced API Documentation**
  - Document endpoint URLs and HTTP methods in API classes
  - This additional context will help developers choose appropriate OAuth scopes

   **API Coverage Expansion**
  - Implement additional Asana API endpoints
  - Add support for missing resource types
  - Ensure comprehensive coverage of core Asana features

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.