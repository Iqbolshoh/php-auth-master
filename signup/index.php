<?php
include '../login/check_cookie.php';

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

    if ($_POST['action'] == 'signup') {
        $first_name = $query->validate($_POST['first_name']);
        $last_name = $query->validate($_POST['last_name']);
        $email = $query->validate(strtolower($_POST['email']));
        $username = $query->validate(strtolower($_POST['username']));
        $password = $_POST['password'];

        // ---- Default Role ---- //
        $role = 'user';
        // ---------------------- //

        if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'title' => 'Validation Error', 'message' => 'All fields are required!']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'title' => 'Email', 'message' => 'Invalid email format!']);
            exit;
        }

        if (!empty($query->select('users', 'email', 'email = ?', [$email], 's'))) {
            echo json_encode(['status' => 'error', 'title' => 'Email', 'message' => 'This email is already registered!']);
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

        if (!empty($query->select('users', 'username', 'username = ?', [$username], 's'))) {
            echo json_encode(['status' => 'error', 'title' => 'Username', 'message' => 'This username is already taken!']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['status' => 'error', 'title' => 'Password', 'message' => 'Password must be at least 8 characters long!']);
            exit;
        }
        $hashed_password = $query->hashPassword($password);

        $query->insert(
            'users',
            [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'username' => $username,
                'password' => $hashed_password,
                'role' => $role,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        );

        $user = $query->select('users', '*', 'username = ?', [$username], 's')[0] ?? null;

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
            echo json_encode(['status' => 'error', 'title' => 'Oops...', 'message' => 'Registration failed. Please try again.']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign Up Page for Registration">
    <meta name="keywords" content="iqbolshoh, iqbolshoh_777, iqbolshoh_dev, iqbolshoh.uz, <?= $_SERVER['HTTP_HOST'] ?>">
    <meta name="author" content="iqbolshoh.uz">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph (OG) tags -->
    <meta property="og:title" content="Signup">
    <meta property="og:description" content="Sign Up Page for Registration">
    <meta property="og:image" content="<?= SITE_PATH ?>/src/images/logo.svg">
    <meta property="og:url" content="<?= SITE_PATH ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $_SERVER['HTTP_HOST'] ?>">

    <title>Sign Up</title>
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
                    <h3 class="text-center mb-3">Sign Up</h3>
                    <form id="signupForm" method="POST">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required
                                maxlength="30">
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required
                                maxlength="30">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required maxlength="100">
                            <small id="email-message" class="text-danger"></small>
                        </div>
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
                            <input type="hidden" name="action" value="signup">
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $query->generate_csrf_token() ?>">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" id="submit" class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="<?= SITE_PATH ?>/login/">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('signupForm');
            const fields = {
                email: document.getElementById('email'),
                username: document.getElementById('username'),
                password: document.getElementById('password')
            };
            const messages = {
                email: document.getElementById('email-message'),
                username: document.getElementById('username-message'),
                password: document.getElementById('password-message')
            };
            const submitBtn = document.getElementById('submit');
            const togglePassword = document.getElementById('toggle-password');

            let availability = { email: false, username: false };

            const validators = {
                email: email => /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email),
                username: username => /^[a-zA-Z0-9_]{3,30}$/.test(username),
                password: password => password.length >= 8
            };

            function checkAvailability(type, value) {
                if (!value) return;
                fetch('check_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `${type}=${encodeURIComponent(value)}`
                })
                    .then(res => res.json())
                    .then(data => {
                        messages[type].textContent = data.exists ? `This ${type} is already taken!` : '';
                        availability[type] = !data.exists;
                        updateSubmitState();
                    });
            }

            function updateSubmitState() {
                const validEmail = fields.email.value.length === 0 || validators.email(fields.email.value) && availability.email;
                const validUsername = fields.username.value.length === 0 || validators.username(fields.username.value) && availability.username;
                const validPassword = fields.password.value.length === 0 || validators.password(fields.password.value);

                const isValid = validEmail && validUsername && validPassword;
                submitBtn.disabled = !isValid;
                submitBtn.style.backgroundColor = isValid ? '#007bff' : '#b8daff';
                submitBtn.style.borderColor = isValid ? '#007bff' : '#b8daff';
                submitBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
            }

            Object.keys(fields).forEach(type => {
                fields[type].addEventListener('input', function () {
                    if (!validators[type](this.value)) {
                        messages[type].textContent = type === 'password' ? 'Password must be at least 8 characters long!' : `Invalid ${type} format!`;
                        availability[type] = false;
                        updateSubmitState();
                        return;
                    }
                    messages[type].textContent = '';
                    if (type !== 'password') checkAvailability(type, this.value);
                    updateSubmitState();
                });
            });

            togglePassword.addEventListener('click', function () {
                fields.password.type = fields.password.type === 'password' ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                try {
                    const response = await fetch('', { method: 'POST', body: new FormData(form) });
                    const data = await response.json();

                    await Swal.fire({
                        icon: data.status === 'success' ? 'success' : 'error',
                        title: data.status === 'success' ? 'Registration successful' : data.title,
                        text: data.message,
                        timer: data.status === 'success' ? 1500 : undefined,
                        showConfirmButton: data.status !== 'success'
                    });

                    if (data.status === 'success') window.location.href = data.redirect;
                } catch (error) {
                    console.error('Fetch error:', error);
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong! Try again.' });
                }
            });
        });
    </script>
</body>

</html>