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
$password     = $_ENV['PASSWORD'];

try {
    $tokenData = AsanaClient::retrieveToken($password);
} catch (JsonException | Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);
$asanaClient->onTokenRefresh(function ($token) use ($asanaClient, $password) {
    $asanaClient->saveToken($password);
});

try {
    $field = $asanaClient->customFields()->getCustomField($_GET['gid']);
    echo '<pre>';
    var_dump($field);
    echo '</pre>';
} catch (TokenInvalidException | AsanaApiException $e) {
    echo $e->getMessage();
}
