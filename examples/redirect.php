<?php
require '../vendor/autoload.php';

use BrightleafDigital\AsanaClient;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri);


if (isset($_GET['code'])) {
    if ($asanaClient->handleCallback($_GET['code'])){
        header('Location: tasks.php');
    } else {
        $authUrl = $asanaClient->getAuthorizationUrl();
        header('Location: ' . $authUrl);
    }
} else {
	$authUrl = $asanaClient->getAuthorizationUrl();
	header('Location: ' . $authUrl);
}
exit;

