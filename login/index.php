<?php
session_start();

include '../config.php';
$query = new Database();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$roles = [
    'admin' => '../admin/',
    'user' => '../'
];

if (!empty($_SESSION['loggedin']) && !empty($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if (isset($roles[$role])) {
        header("Location: {$roles[$role]}");
        exit;
    }
}

if (!empty($_COOKIE['username']) && !empty($_COOKIE['session_token'])) {
    if (session_id() !== $_COOKIE['session_token']) {
        session_write_close();
        session_id($_COOKIE['session_token']);
        session_start();
    }

    $result = $query->select('users', 'id, role', "username = ?", [$_COOKIE['username']], 's');

    if (!empty($result)) {
        $user = $result[0];

        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $_COOKIE['username'];
        $_SESSION['role'] = $user['role'];

        $role = $user['role'];
        if (isset($roles[$role])) {
            header("Location: {$roles[$role]}");
            exit;
        }
    }
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token . "a") {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Security Error',
            text: 'CSRF verification failed! Please reload the page and try again.',
            confirmButtonText: 'OK'
        });
    </script>";
        exit;
    }
    $username = strtolower($_POST['username']);
    $password = $query->hashPassword($_POST['password']);
    $result = $query->select('users', '*', "username = ? AND password = ?", [$username, $password], 'ss');

    if (!empty($result)) {
        $user = $result[0];

        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        setcookie('username', $username, time() + (86400 * 30), "/", "", true, true);
        setcookie('session_token', session_id(), time() + (86400 * 30), "/", "", true, true);

        $redirectPath = $roles[$user['role']];
        ?>

        <script>
            window.onload = function () {
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Login successful',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '<?= $redirectPath; ?>';
                });
            };
        </script>

        <?php
    } else {
        ?>
        <script>
            window.onload = function () {
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: 'Incorrect information',
                    text: 'Login or password is incorrect',
                    showConfirmButton: true
                });
            };
        </script>
        <?php
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
                    <button type="button" id="toggle-password" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
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