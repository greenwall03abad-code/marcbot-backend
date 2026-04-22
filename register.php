<?php
require 'config.php';
$data     = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email    = trim($data['email']    ?? '');
$password =      $data['password'] ?? '';

if (!$username || !$email || !$password) {
    respond(['status' => 'error', 'message' => 'All fields are required'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['status' => 'error', 'message' => 'Invalid email'], 400);
}
if (strlen($password) < 6) {
    respond(['status' => 'error', 'message' => 'Password must be at least 6 characters'], 400);
}

$db   = getDB();
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hash]);
    respond(['status' => 'ok', 'message' => 'Registered successfully']);
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'duplicate') || str_contains($e->getMessage(), 'unique')) {
        respond(['status' => 'error', 'message' => 'Username or email already taken'], 409);
    }
    respond(['status' => 'error', 'message' => 'Registration failed'], 500);
}
