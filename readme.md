# Brightleaf Digital Asana API Client for PHP

[![CI](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/ci.yml/badge.svg)](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/ci.yml)
[![semgrep (php)](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/semgrep.yml/badge.svg)](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/semgrep.yml)

A modern, maintained PHP client library for the Asana API.

## Common development commands

- Lint: `composer lint`
- Auto-fix: `composer lint:fix`
- Test: `composer test`
- Coverage (outputs to build/logs): `composer test:coverage`

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
- Includes built-in token encryption utilities (`CryptoUtils::encrypt` and `CryptoUtils::decrypt`) to help secure 
sensitive token fields during storage. **Meant to be used only in local dev environments. Please use production grade security libraries in production.**
- Includes built-in support for automatic token refreshes, with customizable `onTokenRefresh` events to keep persisted 
storage up to date.



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

### Token Management and Storage Options

The `handleCallback()` method returns an array that contains the token itself, which expires in an hour; the timestamp 
of expiry; a refresh token you can use to get a new access token; and some additional metadata.

This library provides flexibility in how you manage and store tokens. By default, the `saveToken`, `loadToken` and
`retrieveToken` methods offer a simple way for beginners to securely save tokens for future use. However, advanced users have full 
control over token handling and can store their tokens wherever and however they see fit.

#### Built-In Token Storage

> ⚠️ **IMPORTANT SECURITY WARNING**: The `CryptoUtils` class are designed for 
**local development environments only** and are not recommended for production use. For production applications, please 
use a vetted security library or a secure credential management service.

The library provides several methods to manage and persist OAuth tokens. These methods are useful 
for developers looking for a quick and simple way to handle token storage without having to implement custom logic 
from scratch. They are intended for development settings to provide developers with an easy way to store tokens and explore
the library. In production environments more secure methods should be used.

1. **`saveToken`**: Encrypts and stores the current token securely to file storage (default: `token.json` in the working directory).
This ensures sensitive fields like `access_token` and `refresh_token` are safely stored in encrypted form.
2. **`loadToken`**: Reads the encrypted token from storage, decrypts it, and initializes the client for further use. 
If no token is available or decryption fails, the process gracefully returns with a failure.
3. **`retrieveToken`**: Similar to `loadToken`, this static method provides a convenient way to securely load and 
decrypt a stored token **outside the context of an instantiated client**.

>The password you supply to the CryptoUtils::encrypt and decrypt methods should be a regular string password or 
passphrase, not a pre-generated encryption key or binary blob. You can use a strong passphrase (e.g., 
'my-long-dev-password') or store a more complex string (like one from a password manager) in your .env file. This value 
is run through PBKDF2 key derivation with a salt and never used directly as a raw encryption key.

The library's default methods use encryption to protect sensitive fields during storage,
ensuring that tokens are not left exposed in plaintext. Developers still need to safeguard passwords and token files to maintain security.


##### **Automatic Token Refresh Support**
One major improvement in the library is the ability to automatically handle token refreshes and trigger callbacks when 
a token is refreshed. This ensures that tokens remain valid without manual intervention, and any changes to the token 
(after refreshing) are propagated to persistent storage.

```php
use BrightleafDigital\AsanaClient;

$salt = 'your-secure-salt';

// Initialize the client with a stored token
$asanaClient = AsanaClient::withAccessToken('client-id', 'client-secret', AsanaClient::retrieveToken($salt));

// Subscribe to the 'token refreshed' event
$asanaClient->onTokenRefresh(function (array $token) use ($asanaClient, $salt) {
    // Save the refreshed token securely
    $asanaClient->saveToken($salt);

    // Optional: Log or process the refreshed token
    echo "Token refreshed successfully!";
});

// Example API call that triggers a token refresh if the token is expired
$userInfo = $asanaClient->users()->me();
```

- The library **automatically refreshes tokens** when they expire, ensuring uninterrupted API access.
- Developers can subscribe to the **token refresh event** by registering a callback through the `onTokenRefresh` method.
- The callback receives the refreshed token data as a parameter, allowing developers to persist or process the 
updated token as needed.
- You can still refresh the token manually if ever required with the `refreshToken` method.

#### **Flexible Token Handling for Advanced Users**
While the library provides the `saveToken`, `loadToken`, and `retrieveToken` methods for built-in token handling, 
advanced users can (and should) bypass these methods entirely and manage tokens themselves. 
1. Retrieve tokens directly using `$client->getAccessToken()` or upon refresh with `$asanaClient->onTokenRefresh()`.
2. Encrypt tokens using production grade libraries.
3. Store tokens using external methods or third-party services (e.g., databases, cloud secrets management services, etc.).

```php
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Utils\CryptoUtils;

// Retrieve the access token for custom handling
$tokenArray = $asanaClient->getAccessToken();

// Encrypt the token manually
$password = 'your-password';
$encryptedToken = CryptoUtils::encrypt(json_encode($tokenArray), $password); // or just encrypt $tokenArray['access_token'] and $tokenArray['refresh_token']

// Store the encrypted token in a database or a secure location
storeTokenInDatabase($encryptedToken);

// Later: Load and decrypt the token
$storedToken = retrieveTokenFromDatabase();
$tokenData = json_decode(CryptoUtils::decrypt($storedToken, $password), true);

// Initialize the client with the decrypted token
$asanaClient = AsanaClient::withAccessToken('client-id', 'client-secret', $tokenData);
```

#### **Security Best Practices**
When using token storage methods:
1. The token storage file (`token.json`  by default) should have restricted access permissions (e.g., `chmod 600`). 
2. If possible, store sensitive credential files (like `token.json`) in secure locations outside your project directory 
or source control.
3. While your password is meant to be a human-memorable password or passphrase, not a random key or binary blob you still
should keep it safe as an environment variable outside version control. 

#### **Summary of Token Management Methods**

| Method           | Description                                                                                  | Primary Use Case                         |
|------------------|----------------------------------------------------------------------------------------------|------------------------------------------|
| `saveToken`      | Encrypts and saves the current token to a file.                                              | Beginner-friendly token storage.         |
| `loadToken`      | Decrypts and loads the token from storage into the client.                                   | Quick token initialization.              |
| `retrieveToken`  | Static utility to securely load and decrypt tokens for external use.                         | Advanced workflows requiring raw tokens. |
| `onTokenRefresh` | Register a callback to handle token updates after an automatic refresh.                      | Keeping persistent storage up-to-date.   |
| `getAccessToken` | Directly retrieves the current token in its raw array format for manual handling or storage. | Custom storage workflows.                |


If `loadToken()` or `retrieveToken()` fails (e.g., corrupt/missing token file, incorrect password), they return `false` 
or throw an exception. Use this behavior to handle missing tokens gracefully and re-run your OAuth flows if needed.

### Examples
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

## 📘 Project Planning and Improvements

This library is actively developed with long-term maintainability in mind.  
For design decisions, planned features, and deferred items, see the following documentation:

- [Deferred Improvements](./docs/tasks/deferred.md) – Items considered but intentionally postponed
- [Build & Deployment Improvements](./docs/tasks/build-deployment-improvements.md)
- [Code Architecture Improvements](./docs/tasks/code-architecture-improvements.md)
- [Code Quality Improvements](./docs/tasks/code-quality-improvements.md)
- [Documentation Improvements](./docs/tasks/documentation-improvements.md)
- [Feature Additions](./docs/tasks/feature-additions.md)
- [Performance Improvements](./docs/tasks/performance-improvements.md)
- [Security Improvements](./docs/tasks/security-improvements.md)
- [Testing Improvements](./docs/tasks/testing-improvements.md)
- See [Task Summary & Prioritization](docs/tasks/roadmap.md) for a categorized and ranked view of all planned improvements.

Have an idea or want to help implement one of these? Open a [GitHub issue](https://github.com/Eitan-brightleaf/asana-client/issues/new) or submit a pull request.


## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
