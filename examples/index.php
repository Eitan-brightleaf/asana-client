<?php

require '../vendor/autoload.php';

use BrightleafDigital\AsanaClient;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri, __DIR__ . '/token.json');
$asanaClient->loadToken();

if (!$asanaClient->hasToken()) {
    $authUrl = $asanaClient->getAuthorizationUrl();
    header('Location: ' . $authUrl);
} else {
    header('Location: tasks.php');
}
exit;


