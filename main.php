<?php
session_start();

include './config.php';
$query = new Database();
$query->checkUserSession('user');
?>

<?php include './header.php'; ?>

<h2>Iqbolshoh</h2>

<?php include './footer.php'; ?>