<?php

require '../vendor/autoload.php';

use BrightleafDigital\Auth\AsanaOAuthHandler;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);

$authUrl = $authHandler->getAuthorizationUrl();
header('Location: ' . $authUrl);
exit;

