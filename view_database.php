<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4f46e5;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .section {
            margin: 30px 0;
        }
        .completed {
            color: #10b981;
            font-weight: bold;
        }
        .pending {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <h1>Todo App Database Viewer</h1>
    
    <?php
    require_once 'config.php';
    
    $conn = getDBConnection();
    
    if (!$conn) {
        echo "<p style='color: red;'>Error: Could not connect to database.</p>";
        exit;
    }
    
    // Get all users
    echo "<div class='section'>";
    echo "<h2>Users</h2>";
    $stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Created At</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found.</p>";
    }
    echo "</div>";
    
    // Get all tasks
    echo "<div class='section'>";
    echo "<h2>Tasks</h2>";
    $stmt = $conn->query("SELECT t.*, u.username FROM tasks t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tasks) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>User</th><th>Task Text</th><th>Completed</th><th>Created At</th><th>Updated At</th></tr>";
        foreach ($tasks as $task) {
            $completed = $task['completed'] ? '<span class="completed">Yes</span>' : '<span class="pending">No</span>';
            echo "<tr>";
            echo "<td>{$task['id']}</td>";
            echo "<td>{$task['username']}</td>";
            echo "<td>" . htmlspecialchars($task['text']) . "</td>";
            echo "<td>{$completed}</td>";
            echo "<td>{$task['created_at']}</td>";
            echo "<td>{$task['updated_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tasks found.</p>";
    }
    echo "</div>";
    
    // Database info
    echo "<div class='section'>";
    echo "<h2>Database Information</h2>";
    echo "<p><strong>Database File:</strong> " . DB_PATH . "</p>";
    echo "<p><strong>File Size:</strong> " . (file_exists(DB_PATH) ? number_format(filesize(DB_PATH) / 1024, 2) . " KB" : "Not found") . "</p>";
    echo "<p><strong>Total Users:</strong> " . count($users) . "</p>";
    echo "<p><strong>Total Tasks:</strong> " . count($tasks) . "</p>";
    echo "</div>";
    ?>
    
    <p><a href="index.php">← Back to Todo App</a></p>
</body>
</html>

