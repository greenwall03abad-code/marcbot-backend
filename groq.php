<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
require 'config.php';
ob_clean();

$data     = json_decode(file_get_contents('php://input'), true);
$messages = $data['messages'] ?? [];
$system   = $data['system'] ?? 'You are MarcBot, a helpful AI study assistant.';

$token = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $token = str_replace('Bearer ', '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
}

if (empty($token)) {
    echo json_encode(['error' => ['message' => 'Unauthorized']]);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT user_id FROM sessions WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$row  = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['error' => ['message' => 'Invalid or expired token']]);
    exit;
}

$fullMessages = array_merge(
    [['role' => 'system', 'content' => $system]],
    $messages
);

$ch = curl_init(AI_API_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . AI_API_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model'      => AI_MODEL,
    'max_tokens' => 1024,
    'messages'   => $fullMessages
]));
$response = curl_exec($ch);
curl_close($ch);
echo $response;
