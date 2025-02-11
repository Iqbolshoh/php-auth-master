<?php
session_start();

if (($_SESSION['loggedin'] ?? false) !== true || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ./login/");
    exit;
}

include './config.php';
$query = new Database();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sessions</title>
</head>

<body>
    
</body>

</html>