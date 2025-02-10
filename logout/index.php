<?php
session_start();

if (isset($_SESSION['loggedin'])) {
    include '../config.php';
    $query = new Database();
    $session_token = session_id();

    $query->delete('active_sessions', 'session_token = ?', [$session_token], 's');

    session_unset();
    session_destroy();

    $cookies = ['username', 'session_token'];
    foreach ($cookies as $cookie) {
        if (isset($_COOKIE[$cookie])) {
            setcookie($cookie, '', time() - 3600, '/');
        }
    }

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Location: ../login/");
    exit;
}