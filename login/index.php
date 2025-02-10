<?php
session_start();
include '../config.php';

$roles = [
    'admin' => '../admin/',
    'user' => '../'
];

function redirectUser($role)
{
    global $roles;
    if (isset($roles[$role])) {
        header("Location: {$roles[$role]}");
        exit;
    }
}

$query = new Database();

function createSession($user, $query)
{
    $_SESSION = [
        'loggedin' => true,
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ];

    $session_token = session_id();
    $query->insert('active_sessions', [
        'user_id' => $user['id'],
        'device_name' => $_SERVER['HTTP_USER_AGENT'],
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'session_token' => $session_token
    ]);

    setcookie('session_token', $session_token, time() + (86400 * 30), "/", "", true, true);
}

if (!empty($_SESSION['loggedin']) && !empty($_SESSION['role'])) {
    redirectUser($_SESSION['role']);
}

if (!empty($_COOKIE['session_token'])) {
    $result = $query->select('active_sessions', 'user_id', 'session_token = ?', [$_COOKIE['session_token']], 's');
    if (!empty($result)) {
        $user = $query->select('users', 'id, username, role', 'id = ?', [$result[0]['user_id']], 'i');
        if (!empty($user)) {
            createSession($user[0], $query);
            redirectUser($user[0]['role']);
        }
    }
}

if (!empty($_POST['submit'])) {
    $username = strtolower(trim($_POST['username']));
    $password = trim($_POST['password']);

    $result = $query->select('users', '*', 'username = ?', [$username], 's');

    if (!empty($result) && password_verify($password, $result[0]['password'])) {
        createSession($result[0], $query);
        echo "<script>
            window.onload = function () {
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Login successful',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '{$roles[$result[0]['role']]}';
                });
            };
        </script>";
    } else {
        echo "<script>Swal.fire({icon: 'error', title: 'Error', text: 'Invalid username or password', showConfirmButton: true});</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/css/login_signup.css">
    <title>Login</title>
</head>

<body>
    <div class="form-container">
        <h1>Login</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required maxlength="30">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required maxlength="255">
            </div>
            <div class="form-group">
                <button type="submit" name="submit">Login</button>
            </div>
        </form>
        <p>Don't have an account? <a href="../signup/">Sign Up</a></p>
    </div>
    <script src="../src/js/sweetalert2.js"></script>
</body>

</html>