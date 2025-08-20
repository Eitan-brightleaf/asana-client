<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Http\AsanaApiClient;
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

$options = [
    'opt_fields' => 'name',
    'limit' => 100,
];
if (isset($_GET['offset'])) {
    $options['offset'] = $_GET['offset'];
}
try {
    $tasks = $asanaClient->tasks()->getTasksByProject(
        $_GET['project'],
        $options,
        AsanaApiClient::RESPONSE_NORMAL
    );
    $nextPage = $tasks['next_page'] ?? null;
    $tasks = $tasks['data'];

    // Set the starting number where the list should begin
    $startNumber = $_GET['start'] ?? 1; // Use a GET parameter or default to 1

    echo '<ol start="' . htmlspecialchars($startNumber) . '">';
    foreach ($tasks as $task) {
        $href = 'viewTask.php?task=' . urlencode($task['gid']);
        echo '<li><a href="' . htmlspecialchars($href) . '">' . htmlspecialchars($task['name']) . '</a></li>';
    }
    echo '</ol>';
    $project = $asanaClient->projects()->getProject($_GET['project'], ['opt_fields' => 'workspace.gid'])['data'];
    $workspace = $project['workspace']['gid'];

    if (isset($nextPage['offset'])) {
        $currentPageStart = $startNumber + count($tasks); // Calculate the next page's start number
        $href = 'tasks.php?project=' . urlencode($_GET['project'] ?? '')
            . '&offset=' . urlencode($nextPage['offset'])
            . '&start=' . urlencode($currentPageStart);

        echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">Next page</a><br>';
    }

    $href = 'projects.php?workspace=' . urlencode($workspace);
    echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">Back to projects</a>';
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
