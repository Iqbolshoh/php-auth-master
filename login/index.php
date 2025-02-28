<?php
session_start();

include '../config.php';
$query = new Database();

if (!empty($_SESSION['loggedin']) && isset(ROLES[$_SESSION['user']['role']])) {
    header("Location: " . SITE_PATH . ROLES[$_SESSION['user']['role']]);
    exit;
}

if (!empty($_COOKIE['username']) && !empty($_COOKIE['session_token']) && session_id() !== $_COOKIE['session_token']) {
    session_write_close();
    session_id($_COOKIE['session_token']);
    session_start();
}

if (!empty($_COOKIE['username'])) {
    $username = $_COOKIE['username'];
    $user = $query->select('users', '*', "username = ?", [$username], 's')[0] ?? null;

    if (!empty($user)) {
        unset($user['password']);
        $_SESSION['loggedin'] = true;
        $_SESSION['user'] = $user;

        $active_session = $query->select("active_sessions", "*", "session_token = ?", [session_id()], "s");

        if (!empty($active_session)) {
            $query->update(
                "active_sessions",
                ['last_activity' => date('Y-m-d H:i:s')],
                "session_token = ?",
                [session_id()],
                "s"
            );
        }

        if (isset(ROLES[$_SESSION['user']['role']])) {
            header("Location: " . SITE_PATH . ROLES[$_SESSION['user']['role']]);
            exit;
        }
    }
}

function get_user_info()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $devices = [
        'iPhone' => 'iPhone',
        'iPad' => 'iPad',
        'Macintosh|Mac OS X' => 'Mac',
        'Windows NT 10.0' => 'Windows 10 PC',
        'Windows NT 6.3' => 'Windows 8.1 PC',
        'Windows NT 6.2' => 'Windows 8 PC',
        'Windows NT 6.1' => 'Windows 7 PC',
        'Android' => 'Android Phone',
        'Linux' => 'Linux Device',
    ];

    foreach ($devices as $regex => $device) {
        if (stripos($user_agent, $regex) !== false) {
            return $device;
        }
    }

    return "Unknown Device";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST['csrf_token']) &&
        isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        header('Content-Type: application/json');

        $username = strtolower(trim($_POST['username']));
        $password = $query->hashPassword($_POST['password']);

        $user = $query->select('users', '*', "username = ? AND password = ?", [$username, $password], 'ss')[0] ?? null;

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
                'device_name' => get_user_info(),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'last_activity' => date('Y-m-d H:i:s'),
                'session_token' => session_id()
            ]);

            echo json_encode(['status' => 'success', 'redirect' => SITE_PATH . ROLES[$_SESSION['user']['role']]]);
        } else {
            echo json_encode(['status' => 'error', 'title' => 'Oops...', 'message' => 'Login or password is incorrect']);
        }
    } else {
        echo json_encode(['status' => 'error', 'title' => 'Invalid CSRF Token', 'message' => 'Please refresh the page and try again.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?= SITE_PATH ?>/favicon.ico">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                            <input type="hidden" name="csrf_token" value="<?= $query->generate_csrf_token() ?>">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" id="submit" class="btn btn-primary"
                                style="border: none;">Login</button>
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
        document.addEventListener('DOMContentLoaded', function () {
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            const usernameError = document.getElementById('username-message');
            const passwordMessage = document.getElementById('password-message');
            const submitButton = document.getElementById('submit');
            const togglePassword = document.getElementById('toggle-password');
            const loginForm = document.getElementById('loginForm');

            function validateUsername() {
                const username = usernameField.value.trim();
                const usernamePattern = /^[a-zA-Z0-9_]{3,30}$/;

                if (!usernamePattern.test(username)) {
                    usernameError.textContent = "Username must be 3-30 characters: A-Z, a-z, 0-9, or _.";
                    submitButton.disabled = true;
                    submitButton.style.cssText = 'background-color: #b8daff; cursor: not-allowed;';
                    return false;
                } else {
                    usernameError.textContent = "";
                    submitButton.disabled = false;
                    submitButton.style.cssText = 'background-color: #007bff; cursor: pointer;';
                    return true;
                }
            }

            function validatePassword() {
                const password = passwordField.value;
                if (password.length < 8) {
                    passwordMessage.textContent = 'Password must be at least 8 characters long.';
                    submitButton.disabled = true;
                    submitButton.style.cssText = 'background-color: #b8daff; cursor: not-allowed;';
                    return false;
                }
                passwordMessage.textContent = '';
                submitButton.disabled = false;
                submitButton.style.cssText = 'background-color: #007bff; cursor: pointer;';
                return true;
            }

            usernameField.addEventListener('input', validateUsername);
            passwordField.addEventListener('input', validatePassword);

            togglePassword.addEventListener('click', function () {
                passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!validateUsername() || !validatePassword()) {
                    return;
                }

                const formData = new FormData(loginForm);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Login successful',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = data.redirect;
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: data.title,
                                text: data.message,
                                showConfirmButton: true
                            });
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            });
        });
    </script>
</body>

</html>