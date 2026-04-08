<?php
require 'config.php';

$token = getToken();
if (!$token) respond(['status' => 'error', 'message' => 'No token'], 400);

$db   = getDB();
$stmt = $db->prepare("DELETE FROM sessions WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();

respond(['status' => 'ok', 'message' => 'Logged out']);
