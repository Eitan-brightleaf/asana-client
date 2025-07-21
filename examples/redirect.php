<?php

require '../vendor/autoload.php';
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Auth\Scopes;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$password     = $_ENV['PASSWORD'];
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri);

/*
 * Example without state and PKCE
 if ( isset( $_GET['code'] ) ) {
    try {
        $tokenData = $asanaClient->handleCallback( $_GET['code'] );
        if ( $tokenData ) {
            $asanaClient->saveToken();
            header( 'Location: tasks.php' );
            exit;
        }
    } catch ( Exception $e ) {
        die( 'Error during authentication: ' . $e->getMessage() );
    }
}

$authUrl = $asanaClient->getAuthorizationUrl();
header( 'Location: ' . $authUrl );
exit;*/



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the callback includes the authorization code and state
if (isset($_GET['code'], $_GET['state'])) {
    $authorizationCode = $_GET['code'];
    $returnedState = $_GET['state'];
// Retrieve the stored state and PKCE verifier from the session
    $storedState = $_SESSION['oauth2state'] ?? null;
    $pkceVerifier = $_SESSION['oauth2code_verifier'] ?? null;
// Validate that the returned state matches the stored state
    if (!$storedState || $storedState !== $returnedState) {
        die('Invalid state - possible CSRF attack.');
    }

    // Try to obtain an access token using the authorization code, PKCE verifier
    try {
        $tokenData = $asanaClient->handleCallback($authorizationCode, $pkceVerifier);
        if ($tokenData) {
        // Save the token to a file and redirect the user to tasks.php
            $asanaClient->saveToken($password);
            header('Location: workspaces.php');
            exit;
        }
    } catch (Exception $e) {
        die('Error during authentication: ' . $e->getMessage());
    }
}

// If no authorization code or state is available, restart the authentication flow
$scopes = [
    Scopes::ATTACHMENTS_WRITE,
    Scopes::PROJECTS_READ,
    Scopes::TASKS_READ,
    Scopes::TASKS_WRITE,
    Scopes::TASKS_DELETE,
    Scopes::USERS_READ,
    Scopes::WORKSPACES_READ
];
$authData = $asanaClient->getSecureAuthorizationUrl([]); //using default for now
// Store the new state and PKCE verifier in the session
$_SESSION['oauth2_state'] = $authData['state'];
$_SESSION['oauth2_pkce_verifier'] = $authData['codeVerifier'];
// Redirect to the authorization URL
header('Location: ' . $authData['url']);
exit;
