<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$tokenPath = __DIR__ . '/token.json';
$tokenData = json_decode(file_get_contents($tokenPath), true);

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);
$asanaClient->onTokenRefresh(function ($token) use ($tokenPath) {
    file_put_contents($tokenPath, json_encode($token));
});

try {
    $memberships = $asanaClient->memberships()->getMemberships(['parent' => $_GET['project']])['data'];
    echo '<pre>';
    print_r($memberships);
    echo '</pre>';
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
