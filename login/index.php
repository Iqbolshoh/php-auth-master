<?php
session_start();

include '../config.php';
$query = new Database();

if (!empty($_SESSION['loggedin']) && isset(ROLES[$_SESSION['role']])) {
    header("Location: " . SITE_PATH . ROLES[$_SESSION['role']]);
    exit;
}

if (!empty($_COOKIE['username']) && !empty($_COOKIE['session_token']) && session_id() !== $_COOKIE['session_token']) {
    session_write_close();
    session_id($_COOKIE['session_token']);
    session_start();
}

if (!empty($_COOKIE['username'])) {
    $username = $_COOKIE['username'];
    $user = $query->select('users', 'id, role', "username = ?", [$username], 's')[0] ?? null;

    if (!empty($user)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

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

        if (isset(ROLES[$user['role']])) {
            header("Location: " . SITE_PATH . ROLES[$_SESSION['role']]);
            exit;
        }
    }
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

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

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['submit']) &&
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {

    $username = strtolower(trim($_POST['username']));
    $password = $query->hashPassword($_POST['password']);
    $user = $query->select('users', '*', "username = ? AND password = ?", [$username, $password], 'ss')[0] ?? null;

    if (!empty($user)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

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
            'device_name' => get_user_info(),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'last_activity' => date('Y-m-d H:i:s'),
            'session_token' => session_id()
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $redirectPath = SITE_PATH . ROLES[$_SESSION['role']];
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Login successful', timer: 1500, showConfirmButton: false }).then(() => { window.location.href = '<?= $redirectPath; ?>'; }); };
        </script>
        <?php
    } else {
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'error', title: 'Oops...', text: 'Login or password is incorrect', showConfirmButton: true }); };
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
    <link rel="icon" type="image/x-icon" href="<?= SITE_PATH ?>/favicon.ico">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_PATH ?>/src/css/login_signup.css">
</head>

<body>
    <div class="form-container">
        <h1>Login</h1>
        <form id="loginForm" method="POST">
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
                <small id="password-message" style="color: red;"></small>
            </div>
            <div class="form-group">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            </div>
            <div class="form-group">
                <button type="submit" name="submit" id="submit">Login</button>
            </div>
        </form>
        <div class="text-center">
            <p>Don't have an account? <a href="<?= SITE_PATH ?>/signup/">Sign Up</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            const usernameError = document.getElementById('username-error');
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
                    passwordMessage.textContent = 'Invalid email format!';
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
                submitButton.style.backgroundColor = '#b8daff';
                submitButton.style.cursor = 'not-allowed';
                if (!validateUsername() || !validatePassword()) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>