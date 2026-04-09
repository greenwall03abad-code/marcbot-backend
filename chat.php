<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(120);
ignore_user_abort(true);

require 'config.php';

$user_id = requireAuth();

$data    = json_decode(file_get_contents('php://input'), true);
$message = trim($data['message'] ?? '');

if (!$message) {
    respond(['status' => 'error', 'message' => 'Message is required'], 400);
}

$db = getDB();

$stmt = $db->prepare(
    "SELECT role, message FROM chat_history
     WHERE user_id = ? ORDER BY created_at DESC LIMIT 10"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rows = array_reverse($stmt->get_result()->fetch_all(MYSQLI_ASSOC));

$messages = [
    [
        'role'    => 'system',
        'content' => 'You are MarcBot, a helpful and friendly AI study assistant. You explain lessons clearly, concisely, and in an engaging way. You can help with Math, Science, English, History, and general knowledge. Always be encouraging to students.'
    ]
];

foreach ($rows as $row) {
    $messages[] = ['role' => $row['role'], 'content' => $row['message']];
}
$messages[] = ['role' => 'user', 'content' => $message];

$stmt2 = $db->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'user', ?)");
$stmt2->bind_param('is', $user_id, $message);
$stmt2->execute();

$payload = json_encode([
    'model'       => AI_MODEL,
    'messages'    => $messages,
    'max_tokens'  => 1024,
    'temperature' => 0.7,
    'top_p'       => 1,
    'stream'      => false
]);

$ch = curl_init(AI_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . AI_API_KEY,
    ],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err || !$response) {
    respond(['status' => 'error', 'message' => 'Groq error: ' . $err], 502);
}

$result = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    respond(['status' => 'error', 'message' => 'Invalid JSON: ' . substr($response, 0, 200)], 502);
}

$reply = $result['choices'][0]['message']['content'] ?? null;

if (!$reply) {
    respond(['status' => 'error', 'message' => 'No reply. Response: ' . substr($response, 0, 200)], 502);
}

$stmt3 = $db->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, 'assistant', ?)");
$stmt3->bind_param('is', $user_id, $reply);
$stmt3->execute();

respond(['status' => 'ok', 'reply' => $reply]);