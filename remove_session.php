<?php
if (isset($_GET['token'])) {
    $query->delete('active_sessions', 'session_token = ?', [$_GET['token']], 's');
    header('Location: manage_sessions.php');
    exit;
}