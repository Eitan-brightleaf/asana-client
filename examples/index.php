<?php

require '../vendor/autoload.php';

use BrightleafDigital\Auth\AsanaOAuthHandler;
use Dotenv\Dotenv;

$tokenFilePath = __DIR__ . '/token.json';

if (file_exists($tokenFilePath)) {
    // Token file not found, redirect to OAuth flow (index.php)
    header('Location: tasks.php');
    exit;
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);

$authUrl = $authHandler->getAuthorizationUrl();
header('Location: ' . $authUrl);
exit;

