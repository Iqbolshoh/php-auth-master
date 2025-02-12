<?php
session_start();

include '../config.php';
$query = new Database();

if (!empty($_SESSION['loggedin']) && isset(ROLES[$_SESSION['role']])) {
    header("Location: " . ROLES[$_SESSION['role']]);
    exit;
}

if (!empty($_COOKIE['username']) && !empty($_COOKIE['session_token']) && session_id() !== $_COOKIE['session_token']) {
    session_write_close();
    session_id($_COOKIE['session_token']);
    session_start();
}

if (!empty($_COOKIE['username']) && ($user = $query->select('users', 'id, role', "username = ?", [$_COOKIE['username']], 's')[0] ?? null)) {
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['role'] = $user['role'];

    if (isset(ROLES[$user['role']])) {
        header("Location: " . ROLES[$user['role']]);
        exit;
    }
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'], $_POST['csrf_token'])) {

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        echo '<div class="error-message">CSRF error! Please reload the page and try again.</div>';
        exit;
    }

    $username = strtolower(trim($_POST['username']));
    $password = $query->hashPassword($_POST['password']);
    $user = $query->select('users', '*', "username = ? AND password = ?", [$username, $password], 'ss')[0] ?? null;

    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $cookies = [
            'username' => $username,
            'session_token' => session_id()
        ];

        foreach ($cookies as $name => $value) {
            setcookie($name, $value, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        $query->insert('active_sessions', [
            'user_id' => $user['id'],
            'device_name' => $_SERVER['HTTP_USER_AGENT'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'session_token' => session_id()
        ]);

        $redirectPath = ROLES[$user['role']];
        ?>
        <script>
            window.onload = function () { Swal.fire({ position: 'top-end', icon: 'success', title: 'Login successful', showConfirmButton: false, timer: 1500 }).then(() => { window.location.href = '<?= $redirectPath; ?>'; }); };
        </script>
        <?php
    } else {
        ?>
        <script>
            window.onload = function () { Swal.fire({ position: 'top-end', icon: 'error', title: 'Incorrect information', text: 'Login or password is incorrect', showConfirmButton: true }); };
        </script>
        <?php
    }
}else{
    
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
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required maxlength="30">
                <small id="username-error" style="color: red;"></small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required maxlength="255">
                    <button type="button" id="toggle-password" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            </div>
            <div class="form-group">
                <button type="submit" name="submit" id="submit">Login</button>
            </div>
        </form>
        <div class="text-center">
            <p>Don't have an account? <a href="../signup/">Sign Up</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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