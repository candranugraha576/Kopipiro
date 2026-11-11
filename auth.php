<?php
require_once __DIR__ . '/config.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['role_name'] ?? 'guest';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function require_role($expected_role) {
    require_login();
    if ($_SESSION['role_name'] !== $expected_role) {
        http_response_code(403);
        die("<h3 style='color:red; font-family:sans-serif;'>403 Forbidden: You do not have permission to access this page.</h3>");
    }
}
?>