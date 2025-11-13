<?php
header('Content-Type: application/json');
require_once 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => getDatabaseError()]);
    exit;
}

if ($action === 'get') {
    $stmt = $conn->prepare("SELECT id, text, completed FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tasks = [];
    foreach ($rows as $row) {
        $tasks[] = [
            'id' => (string)$row['id'],
            'text' => $row['text'],
            'completed' => (bool)$row['completed']
        ];
    }
    
    echo json_encode(['success' => true, 'tasks' => $tasks]);
    
} elseif ($action === 'add') {
    $text = trim($_POST['text'] ?? '');
    
    if (empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Task text cannot be empty.']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, text, completed) VALUES (?, ?, 0)");
    
    if ($stmt->execute([$userId, $text])) {
        $taskId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'task' => [
                'id' => (string)$taskId,
                'text' => $text,
                'completed' => false
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding task.']);
    }
    
} elseif ($action === 'update') {
    $taskId = $_POST['id'] ?? '';
    $text = trim($_POST['text'] ?? '');
    
    if (empty($taskId) || empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data.']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE tasks SET text = ? WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$text, $taskId, $userId])) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating task.']);
    }
    
} elseif ($action === 'toggle') {
    $taskId = $_POST['id'] ?? '';
    $completed = isset($_POST['completed']) ? 1 : 0;
    
    if (empty($taskId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$completed, $taskId, $userId])) {
        echo json_encode(['success' => true, 'message' => 'Task status updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating task status.']);
    }
    
} elseif ($action === 'delete') {
    $taskId = $_POST['id'] ?? '';
    
    if (empty($taskId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$taskId, $userId])) {
        echo json_encode(['success' => true, 'message' => 'Task deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting task.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>

