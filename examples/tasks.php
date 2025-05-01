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

$options = [
    'opt_fields' => 'name',
    'limit' => 100,
];
if (isset($_GET['offset'])) {
    $options['offset'] = $_GET['offset'];
}
try {
    $tasks = $asanaClient->tasks()->getTasksByProject($_GET['project'], $options);
    $nextPage = $tasks['next_page'] ?? null;
    $tasks = $tasks['data'];

    // Set the starting number where the list should begin
    $startNumber = $_GET['start'] ?? 1; // Use a GET parameter or default to 1

    echo '<ol start="' . $startNumber . '">';
    foreach ($tasks as $task) {
        echo '<li><a href="viewTask.php?task=' . $task['gid'] . '">' . $task['name'] . '</a></li>';
    }
    echo '</ol>';
    $project = $asanaClient->projects()->getProject($_GET['project'], ['opt_fields' => 'workspace.gid'])['data'];
    $workspace = $project['workspace']['gid'];

    if (isset($nextPage['offset'])) {
        $currentPageStart = $startNumber + count($tasks); // Calculate the next page's start number
        echo '<a href="tasks.php?project=' . $_GET['project'] . '&offset=' . $nextPage['offset'] . '
        &start=' . $currentPageStart . '">Next page</a><br>';
    }

    echo '<a href="projects.php?workspace=' . $workspace . '">Back to projects</a>';
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
