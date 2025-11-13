<?php
header('Content-Type: application/json');
require_once 'config.php';

$action = $_POST['action'] ?? '';

if ($action === 'signup') {
    $username = trim(strtolower($_POST['username'] ?? ''));
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username cannot be empty.']);
        exit;
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => getDatabaseError()]);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken.']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO users (username) VALUES (?)");
    
    if ($stmt->execute([$username])) {
        session_start();
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        echo json_encode(['success' => true, 'message' => 'Account created successfully.', 'username' => $username]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating account.']);
    }
    
} elseif ($action === 'login') {
    $username = trim(strtolower($_POST['username'] ?? ''));
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username cannot be empty.']);
        exit;
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => getDatabaseError()]);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Username not found. Please sign up.']);
        exit;
    }
    
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    echo json_encode(['success' => true, 'message' => 'Login successful.', 'username' => $user['username']]);
    
} elseif ($action === 'logout') {
    session_start();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
    
} elseif ($action === 'check') {
    session_start();
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        echo json_encode(['success' => true, 'username' => $_SESSION['username'], 'user_id' => $_SESSION['user_id']]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>

