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

// $pat = $_ENV['PAT'];
// $asanaClient = AsanaClient::withPAT($pat);

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);
$asanaClient->onTokenRefresh(function ($token) use ($tokenPath) {
    file_put_contents($tokenPath, json_encode($token));
});

try {
    $me = $asanaClient->users()->getCurrentUser()['data'];
    $name = $me['name'];
    ?>
        <h1>Hello, <?= $name ?>!</h1>
    <?php
    $workspaces = $asanaClient->workspaces()->getWorkspaces()['data'];
    foreach ($workspaces as $workspace) {
        echo '<a href="projects.php?workspace=' . $workspace['gid'] . '">' . $workspace['name'] . '</a><br>';
    }
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
