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

// $pat = $_ENV['PAT'];
// $asanaClient = AsanaClient::withPAT($pat);

$asanaClient = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData);
$asanaClient->onTokenRefresh(function ($token) use ($asanaClient, $salt) {
    $asanaClient->saveToken($salt);
    //  file_put_contents($tokenPath, json_encode($token));
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
