<?php
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_NAME',     getenv('DB_NAME')     ?: 'marcbot_db');
define('DB_PORT',     getenv('DB_PORT')     ?: 3306);
define('AI_API_URL',  'https://api.groq.com/openai/v1/chat/completions');
define('AI_API_KEY',  getenv('GROQ_API_KEY') ?: '');
define('AI_MODEL',    'llama3-8b-8192');
define('JWT_SECRET',  getenv('JWT_SECRET')  ?: 'marcbot-secret-change-this');
define('ALLOWED_ORIGIN', getenv('FRONTEND_URL') ?: '*');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($db->connect_error) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
            exit;
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}
function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
function getToken() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? '';
    if (preg_match('/Bearer\s+(.+)/', $auth, $m)) return $m[1];
    return null;
}
function requireAuth() {
    $token = getToken();
    if (!$token) respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
    $db = getDB();
    $stmt = $db->prepare("SELECT user_id FROM sessions WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) respond(['status' => 'error', 'message' => 'Session expired'], 401);
    return $row['user_id'];
}
