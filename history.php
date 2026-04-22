<?php
require_once 'config.php';
$user_id = requireAuth();
$db = getDB();

$stmt = $db->prepare('SELECT role, message, created_at FROM chat_history WHERE user_id = ? ORDER BY created_at ASC LIMIT 100');
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

respond(['status' => 'ok', 'history' => $rows]);
