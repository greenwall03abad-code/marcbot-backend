<?php
define('DB_HOST',     getenv('DB_HOST')     ?: 'aws-1-ap-south-1.pooler.supabase.com');
define('DB_USER',     getenv('DB_USER')     ?: 'postgres.ltzvyjmswaxgnbnehhfl');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_NAME',     getenv('DB_NAME')     ?: 'postgres');
define('DB_PORT',     getenv('DB_PORT')     ?: '5432');
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
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        try {
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
            exit;
        }
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
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) respond(['status' => 'error', 'message' => 'Session expired'], 401);
    return $row['user_id'];
}
