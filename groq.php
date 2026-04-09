<?php
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$messages = $data['messages'] ?? [];
$system = $data['system'] ?? 'You are MarcBot, a helpful AI study assistant.';

// Simple token check
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['error' => ['message' => 'Unauthorized']]);
    exit;
}

// Verify token sa database
$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => ['message' => 'Invalid token']]);
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
    'model' => AI_MODEL,
    'max_tokens' => 1024,
    'messages' => $fullMessages
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
echo $response;