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

if ($asanaClient->loadToken($password)) {
    header('Location: workspaces.php');
    exit;
}

/*$authUrl = $asanaClient->getAuthorizationUrl();
header('Location: ' . $authUrl);
exit;*/

$scopes = [
    Scopes::ATTACHMENTS_WRITE,
    Scopes::PROJECTS_READ,
    Scopes::TASKS_READ,
    Scopes::TASKS_WRITE,
    Scopes::TASKS_DELETE,
    Scopes::USERS_READ,
    Scopes::WORKSPACES_READ
];
$authUrl = $asanaClient->getSecureAuthorizationUrl([]); //using default for now
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['oauth2state'] = $authUrl['state'];
$_SESSION['oauth2code_verifier'] = $authUrl['codeVerifier'];

header('Location: ' . $authUrl['url']);
exit;
