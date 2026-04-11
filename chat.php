<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once 'config.php';

$token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
if (!$token) { echo json_encode(['status'=>'error','message'=>'Unauthorized.']); exit(); }

$stmt = $pdo->prepare('SELECT user_id FROM sessions WHERE token = ?');
$stmt->execute([$token]);
$session = $stmt->fetch();
if (!$session) { echo json_encode(['status'=>'error','message'=>'Unauthorized.']); exit(); }

$data = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');
$role = trim($data['role'] ?? 'user');

if (!$message) { echo json_encode(['status'=>'error','message'=>'No message.']); exit(); }

$stmt = $pdo->prepare('INSERT INTO chat_history (user_id, role, message) VALUES (?, ?, ?)');
$stmt->execute([$session['user_id'], $role, $message]);

echo json_encode(['status'=>'ok']);
