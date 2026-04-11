<?php
require_once 'config.php';

$user_id = requireAuth();
$db = getDB();

$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');
$role = trim($data['role'] ?? 'user');

if (!$message) respond(['status' => 'error', 'message' => 'No message.']);

$stmt = $db->prepare('INSERT INTO chat_history (user_id, role, message) VALUES (?, ?, ?)');
$stmt->bind_param('iis', $user_id, $role, $message);
$stmt->execute();

respond(['status' => 'ok']);
