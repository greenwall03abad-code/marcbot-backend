<?php
require 'config.php';
$data     = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email']    ?? '');
$password =      $data['password'] ?? '';

if (!$email || !$password) {
    respond(['status' => 'error', 'message' => 'Email and password required'], 400);
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, username, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    respond(['status' => 'error', 'message' => 'Invalid credentials'], 401);
}

$token   = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+7 days'));

$stmt2 = $db->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt2->execute([$user['id'], $token, $expires]);

respond([
    'status'   => 'ok',
    'token'    => $token,
    'user_id'  => $user['id'],
    'username' => $user['username'],
]);
