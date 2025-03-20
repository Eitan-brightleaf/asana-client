<?php
require '../vendor/autoload.php';

use BrightleafDigital\Auth\AsanaOAuthHandler;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$clientId = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri = $_ENV['ASANA_REDIRECT_URI'];
$authHandler = new AsanaOAuthHandler($clientId, $clientSecret, $redirectUri);


if (isset($_GET['code'])) {
	$authorizationCode = $_GET['code'];
	$accessToken = $authHandler->getAccessToken($authorizationCode);
	// $accessToken is now an array containing the token, expiration, etc.

	$encodedToken = json_encode( $accessToken );
	$tokenFilePath = __DIR__ . '/token.json';
	file_put_contents($tokenFilePath, $encodedToken);
	?>
	<h1>
		Success!
		<br>
		<a href="tasks.php" >Go to tasks page</a>
	</h1>
<?php
} else {
	$authUrl = $authHandler->getAuthorizationUrl();
	header('Location: ' . $authUrl);
	exit;
}

