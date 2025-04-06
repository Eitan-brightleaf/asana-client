<?php

require '../vendor/autoload.php';

use BrightleafDigital\AsanaClient;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri);
$asanaClient->loadToken();

if ($asanaClient->hasToken()) {
    header('Location: workspaces.php');
    exit;
}

/*$authUrl = $asanaClient->getAuthorizationUrl();
header('Location: ' . $authUrl);
exit;*/

$authUrl = $asanaClient->getSecureAuthorizationUrl();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['oauth2state'] = $authUrl['state'];
$_SESSION['oauth2code_verifier'] = $authUrl['codeVerifier'];

header('Location: ' . $authUrl['url']);
exit;
