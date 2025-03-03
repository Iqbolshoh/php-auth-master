<?php
include 'check_cookie.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        echo json_encode(['status' => 'error', 'title' => 'Invalid CSRF Token', 'message' => 'Invalid CSRF token!']);
        exit;
    }
    header('Content-Type: application/json');

    if ($_POST['action'] == 'login') {
        $username = trim(strtolower($_POST['username']));
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'title' => 'Validation Error', 'message' => 'All fields are required!']);
            exit;
        }

        if (strlen($username) < 3) {
            echo json_encode(['status' => 'error', 'title' => 'Username', 'message' => 'Username must be at least 3 characters long!']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            echo json_encode(['status' => 'error', 'title' => 'Username', 'message' => 'Username must be 3-30 characters: A-Z, a-z, 0-9, or _!']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['status' => 'error', 'title' => 'Password', 'message' => 'Password must be at least 8 characters long!']);
            exit;
        }
        $hashed_password = $query->hashPassword($password);

        $user = $query->select('users', '*', 'username = ? AND password = ?', [$username, $hashed_password], 'ss')[0] ?? null;

        if (!empty($user)) {
            unset($user['password']);
            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = $user;

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
                'user_id' => $_SESSION['user']['id'],
                'device_name' => get_device_name(),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'last_activity' => date('Y-m-d H:i:s'),
                'session_token' => session_id()
            ]);

            echo json_encode(['status' => 'success', 'redirect' => SITE_PATH . ROLES[$_SESSION['user']['role']]]);
        } else {
            echo json_encode(['status' => 'error', 'title' => 'Oops...', 'message' => 'Login or password is incorrect']);
        }
        exit;
    }
}
$query->generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Account Login Page">
    <meta name="keywords" content="iqbolshoh, iqbolshoh_777, iqbolshoh_dev, iqbolshoh.uz, <?= $_SERVER['HTTP_HOST'] ?>">
    <meta name="author" content="iqbolshoh.uz">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph (OG) tags -->
    <meta property="og:title" content="Signup">
    <meta property="og:description" content="Account Login Page">
    <meta property="og:image" content="<?= SITE_PATH ?>/src/images/logo.svg">
    <meta property="og:url" content="<?= SITE_PATH ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $_SERVER['HTTP_HOST'] ?>">

    <title>Login</title>
    <link rel="icon" href="<?= SITE_PATH . "/favicon.ico" ?>" type="image/x-icon">

    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card p-4 shadow-lg rounded-4">
                    <div class="text-center">
                        <a href="https://iqbolshoh.uz" target="_blank">
                            <img src="<?= SITE_PATH ?>/src/images/logo.svg" alt="Logo" style="width: 120px;">
                        </a>
                    </div>
                    <h3 class="text-center mb-3">Login</h3>
                    <form id="loginForm" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required
                                maxlength="30">
                            <small id="username-message" class="text-danger"></small>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" required
                                    maxlength="255">
                                <button type="button" id="toggle-password" class="btn btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small id="password-message" class="text-danger"></small>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="action" value="login">
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" id="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="<?= SITE_PATH ?>/signup/">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            const usernameError = document.getElementById('username-message');
            const passwordMessage = document.getElementById('password-message');
            const togglePassword = document.getElementById('toggle-password');
            const loginForm = document.getElementById('loginForm');

            const validateUsername = () => {
                const username = usernameField.value.trim();
                const isValid = /^[a-zA-Z0-9_]{3,30}$/.test(username);
                usernameError.textContent = isValid ? '' : 'Username must be 3-30 characters: A-Z, a-z, 0-9, or _.';
                return isValid;
            };

            const validatePassword = () => {
                const isValid = passwordField.value.length >= 8;
                passwordMessage.textContent = isValid ? '' : 'Password must be at least 8 characters long.';
                return isValid;
            };

            usernameField.addEventListener('input', validateUsername);
            passwordField.addEventListener('input', validatePassword);

            togglePassword.addEventListener('click', () => {
                passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
                togglePassword.querySelector('i').classList.toggle('fa-eye');
                togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
            });

            loginForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                try {
                    const formData = new FormData(loginForm);
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });

                    const responseText = await response.text();
                    const data = JSON.parse(responseText);

                    Swal.fire({
                        icon: data.status === 'success' ? 'success' : 'error',
                        title: data.status === 'success' ? 'Login successful' : data.title,
                        text: data.message || '',
                        timer: data.status === 'success' ? 1500 : undefined,
                        showConfirmButton: data.status !== 'success'
                    }).then(() => {
                        if (data.status === 'success') window.location.href = data.redirect;
                    });

                } catch (error) {
                    console.error('Fetch error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again later.',
                        showConfirmButton: true
                    });
                }
            });
        });
    </script>
</body>

</html>