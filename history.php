<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once 'config.php';

$token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
if (!$token) { echo json_encode(['status'=>'error','message'=>'Unauthorized.']); exit(); }

$stmt = $pdo->prepare('SELECT user_id FROM sessions WHERE token = ?');
$stmt->execute([$token]);
$session = $stmt->fetch();

if (!$session) { echo json_encode(['status'=>'error','message'=>'Unauthorized.']); exit(); }

$stmt = $pdo->prepare('SELECT role, message, created_at FROM chat_history WHERE user_id = ? ORDER BY created_at ASC LIMIT 100');
$stmt->execute([$session['user_id']]);
$rows = $stmt->fetchAll();

echo json_encode(['status'=>'ok','history'=>$rows]);
