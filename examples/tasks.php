<?php

use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Http\AsanaApiClient;
use Dotenv\Dotenv;
use League\OAuth2\Client\Token\AccessToken;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri  = $_ENV['ASANA_REDIRECT_URI'];
$authHandler  = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);

$tokenFilePath = __DIR__ . '/token.json';

if (!file_exists($tokenFilePath)) {
	// Token file not found, redirect to OAuth flow (index.php)
	header('Location: index.php');
	exit;
}

$tokenData = json_decode(file_get_contents($tokenFilePath), true);
$accessToken = new AccessToken($tokenData);

// Initialize the OAuth handler
$authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);

// Check if the token is expired
if ($accessToken->hasExpired()) {
	try {
		// Refresh the token
		$newAccessToken = $authHandler->refreshToken($accessToken);

		// Save the refreshed token to token.json
		file_put_contents($tokenFilePath, json_encode($newAccessToken->jsonSerialize()));

		// Use the refreshed token
		$accessToken = $newAccessToken;
	} catch (\Exception $e) {
		// Redirect to index.php if refreshing token fails
		header('Location: index.php');
		exit;
	}
}

// Use the valid token to retrieve some tasks
$asanaClient = new AsanaApiClient($accessToken->getToken());
$taskApiService = new TaskApiService($asanaClient);

try {
	$projectGid = $_ENV['PROJECT_GID'];
	// Replace 'project-id' with your actual Asana project ID
	$tasks = $taskApiService->getTasksByProject($projectGid);
	echo '<pre>';
	print_r($tasks);
	echo '</pre>';
} catch (\Exception $e) {
	echo 'Error: ' . $e->getMessage();
}
