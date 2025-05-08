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
$salt = $_ENV['SALT'];
try {
    $tokenData = AsanaClient::retrieveToken($salt);
} catch (JsonException | Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);
$asanaClient->onTokenRefresh(function ($token) use ($asanaClient, $salt) {
    $asanaClient->saveToken($salt);
});

try {
    $projects = $asanaClient->projects()->getProjects($_GET['workspace'])['data'];

    foreach ($projects as $project) {
        echo '<h3>' . $project['name'] . '</h3>';
        echo '<a href="tasks.php?project=' . $project['gid'] . '">Tasks</a><br>';
        echo '<a href="sections.php?project=' . $project['gid'] . '">Sections</a><br>';
        echo '<a href="memberships.php?project=' . $project['gid'] . '">Memberships</a><br>';
        echo '<a href="createTask.php?project=' . $project['gid'] . '">Create Task</a><br>';
        echo '<a href="customFields.php?project=' . $project['gid'] . '">Custom Fields</a><br>';
        echo '<hr>';
    }
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
