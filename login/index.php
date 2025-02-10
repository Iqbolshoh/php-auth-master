<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include '../config.php';
$query = new Database();

$roles = [
    'admin' => '../admin/',
    'user' => '../'
];


if (isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        die('CSRF verification failed!');
    }
    $username = strtolower($_POST['username']);
    $password = $query->hashPassword($_POST['password']);
    $result = $query->select('users', '*', "username = ? AND password = ?", [$username, $password], 'ss');

    if (!empty($result)) {
        $user = $result[0];

        $_SESSION = [
            'loggedin' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

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
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
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