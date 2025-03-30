<?php

use BrightleafDigital\AsanaClient;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri  = $_ENV['ASANA_REDIRECT_URI'];

$tokenData = json_decode(file_get_contents(__DIR__ . '/token.json'), true);

$asanaClient = AsanaClient::withAccessToken( $clientId, $clientSecret, $tokenData );

try {
    $projectGid = $_ENV['PROJECT_GID'];
    $tasks = $asanaClient->tasks()->getTasksByProject($projectGid);
    echo '<pre>';
    print_r($tasks);
    echo '</pre>';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}