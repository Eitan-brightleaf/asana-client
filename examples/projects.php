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
    $projects = $asanaClient->projects()->getProjects(
        $_GET['workspace']
    );

    foreach ($projects as $project) {
        echo '<h3>' . htmlspecialchars($project['name']) . '</h3>';

        $urlEncodedGid = urlencode($project['gid']);

        $href = "tasks.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Tasks</a><br>';

        $href = "sections.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Sections</a><br>';

        $href = "memberships.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Memberships</a><br>';

        $href = "createTask.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Create Task</a><br>';

        $href = "customFields.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Custom Fields</a><br>';
        echo '<hr>';
    }
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
