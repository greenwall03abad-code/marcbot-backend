<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(120);
ignore_user_abort(true);

require 'config.php';

$user_id = requireAuth();

$payload = json_encode([
    'model'      => AI_MODEL,
    'messages'   => [['role' => 'user', 'content' => 'Say hello']],
    'max_tokens' => 50,
    'stream'     => false
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
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

respond(['status' => 'ok', 'reply' => 'Step 3 - HTTP: ' . $code . ' Error: ' . $err . ' Response: ' . substr($response, 0, 200)]);