<?php
ini_set('display_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');
error_reporting(E_ALL);
set_time_limit(120);
ignore_user_abort(true);

require 'config.php';

// Test - return early to check if basic PHP works
respond(['status' => 'ok', 'reply' => 'Step 1: PHP works']);