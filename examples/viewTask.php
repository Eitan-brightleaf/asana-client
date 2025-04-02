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



try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['task_gid'])) {
        $taskGid = $_POST['task_gid'];
        if ($_POST['action'] === 'delete') {
            $asanaClient->tasks()->deleteTask($taskGid);
            header('Location: tasks.php?project=' . $_POST['project_gid']);
            exit;
        } elseif ($_POST['action'] === 'complete') {
            $asanaClient->tasks()->updateTask($taskGid, ['completed' => true]);
            header('Location: tasks.php?project=' . $_POST['project_gid']);
            exit;
        }
    }

    $task = $asanaClient->tasks()->getTask($_GET['task']);
    echo '<pre>';
    print_r($task);
    echo '</pre>';

    echo '<form method="POST" 
        style="display: inline-block; margin-right: 10px;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="task_gid" value="' . htmlspecialchars($task['gid']) . '">
        <input type="hidden" name="project_gid" value="' . $task['projects'][0]['gid'] . '">
        <button type="submit" 
        onclick="return confirm(\'Are you sure you want to delete this task?\')">Delete Task</button>
    </form>';

    echo '<form method="POST" style="display: inline-block; margin-right: 10px;">
        <input type="hidden" name="action" value="complete">
        <input type="hidden" name="task_gid" value="' . htmlspecialchars($task['gid']) . '">
        <input type="hidden" name="project_gid" value="' . $task['projects'][0]['gid'] . '">
        <button type="submit">Complete Task</button>
    </form>';

    echo '<p><a href="tasks.php?project=' .
        htmlspecialchars($task['projects'][0]['gid']) . '">Back to task list</a></p>';
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
