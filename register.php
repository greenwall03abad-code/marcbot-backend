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

$stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $username, $email, $hash);

if (!$stmt->execute()) {
    if ($db->errno === 1062) {
        respond(['status' => 'error', 'message' => 'Username or email already taken'], 409);
    }
    respond(['status' => 'error', 'message' => 'Registration failed'], 500);
}

respond(['status' => 'ok', 'message' => 'Registered successfully']);
