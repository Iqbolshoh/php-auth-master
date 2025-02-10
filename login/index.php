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
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../src/css/login_signup.css">
</head>

<body>
    <div class="form-container">
        <h1>Login</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required maxlength="30">
                <small id="username-error" style="color: red;"></small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required maxlength="255">
                    <button type="button" id="toggle-password" class="password-toggle"><i
                            class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" name="submit" id="submit" disabled>Login</button>
            </div>
        </form>
        <div class="text-center">
            <p>Don't have an account? <a href="../signup/">Sign Up</a></p>
        </div>
    </div>
    <script src="../src/js/sweetalert2.js"></script>
    <script>
        const usernameField = document.getElementById('username');
        const usernameError = document.getElementById('username-error');
        const submitButton = document.getElementById('submit');

        function validateForm() {
            const username = usernameField.value;
            const usernamePattern = /^[a-zA-Z0-9_]+$/;
            if (!usernamePattern.test(username)) {
                usernameError.textContent = "Username can only contain letters, numbers, and underscores!";
                submitButton.disabled = true;
            } else {
                usernameError.textContent = "";
                submitButton.disabled = false;
            }
        }

        usernameField.addEventListener('input', validateForm);

        document.getElementById('toggle-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const toggleIcon = this.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>