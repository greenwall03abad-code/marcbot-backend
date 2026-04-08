<?php
require 'config.php';

$user_id = requireAuth();
$db      = getDB();

$stmt = $db->prepare(
    "SELECT role, message, created_at FROM chat_history
     WHERE user_id = ? ORDER BY created_at ASC LIMIT 100"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

respond(['status' => 'ok', 'history' => $rows]);
