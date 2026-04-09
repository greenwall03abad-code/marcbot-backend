<?php
require 'config.php';

// Verify token
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (!str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => ['message' => 'Unauthorized']]);
    exit;
}
$token = substr($authHeader, 7);

// Verify JWT token
$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => ['message' => 'Invalid token']]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$messages = $data['messages'] ?? [];
$system = $data['system'] ?? 'You are MarcBot, a helpful AI study assistant.';

// Prepend system message
$fullMessages = array_merge(
    [['role' => 'system', 'content' => $system]],
    $messages
);

// Call Groq API using config constants
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