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

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['submit']) &&
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {

    $first_name = $query->validate($_POST['first_name']);
    $last_name = $query->validate($_POST['last_name']);
    $email = $query->validate(strtolower($_POST['email']));
    $username = $query->validate(strtolower($_POST['username']));
    $password = $query->hashPassword($_POST['password']);
    $role = 'user'; // default role is 'user'

    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'username' => $username,
        'password' => $password,
        'role' => $role
    ];

    $user = $query->insert('users', $data);

    if (!empty($user)) {
        $user_id = $query->select('users', 'id', 'username = ?', [$username], 's')[0]['id'];

        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

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
            'user_id' => $user_id,
            'device_name' => $_SERVER['HTTP_USER_AGENT'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'session_token' => session_id()
        ]);

        $redirectPath = ROLES[$role];
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Registration successfu', timer: 1500, showConfirmButton: false }).then(() => { window.location.href = '<?= $redirectPath; ?>'; }); };
        </script>
        <?php
    } else {
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'error', title: 'Oops...', text: 'Registration failed. Please try again.', showConfirmButton: true }); };
        </script>
        <?php
    }
} elseif (isset($_POST['submit'])) {
    ?>
    <script>
        window.onload = function () { Swal.fire({ icon: 'error', title: 'Invalid CSRF Token', text: 'Please refresh the page and try again.', showConfirmButton: true }); };
    </script>
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../src/css/login_signup.css">
</head>

<body>
    <div class="form-container">
        <h1>Sign Up</h1>
        <form id="signupForm" method="POST" action="">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required maxlength="30">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required maxlength="30">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required maxlength="100">
                <p id="email-message"></p>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required maxlength="30">
                <p id="username-message"></p>
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
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            </div>
            <div class="form-group">
                <button type="submit" name="submit" id="submit">Login</button>
            </div>
        </form>
        <div class="text-center">
            <p>Already have an account? <a href="../login/">Login</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let isEmailAvailable = false;
        let isUsernameAvailable = false;

        function validateUsernameFormat(username) {
            const usernamePattern = /^[a-zA-Z0-9_]+$/;
            return usernamePattern.test(username);
        }

        function validateEmailFormat(email) {
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            return emailPattern.test(email);
        }

        function checkAvailability(type, value, messageElement, callback) {
            fetch('check_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `${type}=${encodeURIComponent(value)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        messageElement.textContent = `This ${type} exists!`;
                        callback(false);
                    } else {
                        messageElement.textContent = '';
                        callback(true);
                    }
                });
        }

        document.getElementById('email').addEventListener('input', function () {
            const email = this.value;
            const emailMessageElement = document.getElementById('email-message');

            if (!validateEmailFormat(email)) {
                emailMessageElement.textContent = 'Email format is incorrect!';
                isEmailAvailable = false;
                return;
            }

            checkAvailability('email', email, emailMessageElement, status => isEmailAvailable = status);
        });

        document.getElementById('username').addEventListener('input', function () {
            const username = this.value;
            const usernameMessageElement = document.getElementById('username-message');

            if (!validateUsernameFormat(username)) {
                usernameMessageElement.textContent = 'Username can only contain letters, numbers, and underscores!';
                isUsernameAvailable = false;
                return;
            }

            checkAvailability('username', username, usernameMessageElement, status => isUsernameAvailable = status);
        });

        document.getElementById('signupForm').addEventListener('submit', function (event) {
            const email = document.getElementById('email').value;
            const emailMessageElement = document.getElementById('email-message');
            const username = document.getElementById('username').value;
            const usernameMessageElement = document.getElementById('username-message');

            if (!validateEmailFormat(email)) {
                emailMessageElement.textContent = 'Email format is incorrect!';
                event.preventDefault();
            }

            if (!validateUsernameFormat(username)) {
                usernameMessageElement.textContent = 'Username can only contain letters, numbers, and underscores!';
                event.preventDefault();
            }

            if (!isEmailAvailable) {
                emailMessageElement.textContent = 'This email exists!';
                event.preventDefault();
            }

            if (!isUsernameAvailable) {
                usernameMessageElement.textContent = 'This username exists!';
                event.preventDefault();
            }
        });

        document.getElementById('toggle-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const toggleIcon = this.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>

</html>