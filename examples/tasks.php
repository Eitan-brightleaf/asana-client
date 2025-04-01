<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\AsanaApiException;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$tokenPath = __DIR__ . '/token.json';
$tokenData = json_decode(file_get_contents($tokenPath), true);

// $pat = $_ENV['PAT'];
// $asanaClient = AsanaClient::withPAT($pat);

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);

try {
    $projectGid = $_ENV['PROJECT_GID'];
    $tasks = $asanaClient->tasks()->getTasksByProject($projectGid, [
            'opt_fields' => 'name,due_at,due_on,
		    completed,assignee.name,notes'
        ]);
    echo '<pre>';
    print_r($tasks);
    echo '</pre>';
} catch (AsanaApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
