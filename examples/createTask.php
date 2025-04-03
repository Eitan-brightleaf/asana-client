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
    $projectGid = $_GET['project'] ?? null;
    if (!$projectGid) {
        throw new InvalidArgumentException('Project parameter is required');
    }

    $project = $asanaClient->projects()->getProject($projectGid, ['opt_fields' => 'workspace.gid'])['data'];
    $workspace = $project['workspace']['gid'];

    $projects = $asanaClient->projects()->getProjects($workspace)['data'];
    $users = $asanaClient->users()->getUsers($workspace)['data'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            $taskGid = $_POST['task_gid'];
            if ($_POST['action'] === 'delete') {
                $asanaClient->tasks()->deleteTask($taskGid);
                echo '<h1>Task deleted successfully!</h1>';
                echo '<p><a href="?project=' . htmlspecialchars($projectGid) . '">Create new task</a></p>';
                exit;
            } elseif ($_POST['action'] === 'complete') {
                $asanaClient->tasks()->updateTask($taskGid, ['completed' => true]);
                echo '<h1>Task completed successfully!</h1>';
                echo '<p><a href="?project=' . htmlspecialchars($projectGid) . '">Create new task</a></p>';
                exit;
            }
        }

        $data = [
            'name' => $_POST['name'],
            'notes' => $_POST['notes'],
            'projects' => $_POST['projects'],
            'assignee' => $_POST['assignee'], // can also just be 'me' if you want to assign to yourself
            'workspace' => $workspace,
            'due_on' => $_POST['due_on']
        ];
        $task = $asanaClient->tasks()->createTask(
            $data,
            [
                'opt_fields' => 'name,notes,due_on,assignee.name,projects.name,created_at,modified_at,permalink_url',
                'opt_pretty' => true
            ]
        );
        if ($task) {
            echo '<h1>Task created successfully!</h1>';
            echo '<pre>';

            // Convert permalink_url to clickable link before displaying
            if (isset($task['permalink_url'])) {
                $task['permalink_url'] = '<a href="' . htmlspecialchars($task['permalink_url']) . '" target="_blank">' .
                    htmlspecialchars($task['permalink_url']) . '</a>';
            }
            print_r($task);
            echo '</pre>';

            echo '<form method="POST" style="display: inline-block; margin-right: 10px;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="task_gid" value="' . htmlspecialchars($task['gid']) . '">
                <button type="submit" 
                onclick="return confirm(\'Are you sure you want to delete this task?\')">Delete Task</button>
            </form>';

            echo '<form method="POST" style="display: inline-block; margin-right: 10px;">
                <input type="hidden" name="action" value="complete">
                <input type="hidden" name="task_gid" value="' . htmlspecialchars($task['gid']) . '">
                <button type="submit">Complete Task</button>
            </form>';

            echo '<p><a href="uploadAttachment.php?task=' . htmlspecialchars($task['gid']) . '">
                    Upload Attachment To Task</a></p>';
            echo '<p><a href="?project=' . htmlspecialchars($projectGid) . '">Create new task</a></p>';
            echo '<p><a href="tasks.php?project=' . htmlspecialchars($projectGid) . '">View all tasks</a></p>';
            exit;
        }
    }
    ?>
    <form method="POST">
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"></textarea>
        </div>
        <div>
            <label for="due_on">Due Date:</label>
            <input type="date" id="due_on" name="due_on">
        </div>
        <div>
            <label for="projects">Projects:</label>
            <select id="projects" name="projects[]" multiple required>
                <?php foreach ($projects as $project) : ?>
                    <option
                        value="<?php echo $project['gid']; ?>">
                        <?php echo htmlspecialchars($project['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="assignee">Assignee:</label>
            <select id="assignee" name="assignee">
                <option value="">Select assignee</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['gid']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Create Task</button>
    </form>
    <?php
} catch (AsanaApiException | TokenInvalidException $e) {
    echo 'Error: ' . $e->getMessage();
}
