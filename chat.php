<?php
ini_set('display_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');
error_reporting(E_ALL);
set_time_limit(120);
ignore_user_abort(true);

require 'config.php';

$user_id = requireAuth();

// Test - check if auth works
respond(['status' => 'ok', 'reply' => 'Step 2: Auth works! User ID: ' . $user_id]);