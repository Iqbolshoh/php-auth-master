<?php
session_start();
include './config.php';

$query = new Database();
$query->checkUserSession('user');

$user = $query->select("users", '*', "id = ?", [$_SESSION['user_id']], 'i');
$user = $user ? $user[0] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $query->validate($_POST['first_name']);
    $last_name = $query->validate($_POST['last_name']);
    $email = $query->validate(strtolower($_POST['email']));

    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email
    ];

    if (isset($_POST['password'])) {
        $data['password'] = $query->hashPassword($_POST['password']);
    }

    $query->update(
        "users",
        $data,
        "id = ?",
        [$_SESSION['user_id']],
        "i"
    );
    ?>
    <script>
        window.onload = function () { Swal.fire({ icon: 'success', title: 'Success!', text: 'Your profile has been updated successfully!', timer: 1500, showConfirmButton: false }).then(() => { window.location.href = 'index.php'; }); };
    </script>
    <?php
    exit;
} ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<style>
    .form-group .password-container {
        display: flex;
        align-items: center;
    }

    .form-group .password-container input {
        flex: 1;
        padding-right: 40px;
    }

    .form-group .password-toggle {
        position: absolute;
        right: 10px;
        font-size: 18px;
        cursor: pointer;
        border: none;
        background: transparent;
    }
</style>

<body class="hold-transition sidebar-mini">
    <script>
        window.onload = function () { Swal.fire({ icon: 'success', title: 'Success!', text: 'Your profile has been updated successfully!', timer: 1500, showConfirmButton: false }).then(() => { window.location.href = 'index.php'; }); };
    </script>
    <div class="wrapper">
        <?php include './header.php'; ?>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">

                    <form method="POST">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control"
                                value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control"
                                value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control"
                                value="<?php echo htmlspecialchars($user['username']); ?>" required disabled>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-container">
                                <input type="password" id="password" name="password" class="form-control"
                                    maxlength="255">
                                <button type="button" id="toggle-password" class="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </section>

        </div>
        <?php include './footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
    <script src="./src/js/jquery.min.js"></script>
    <script src="./src/js/bootstrap.bundle.min.js"></script>
    <script src="./src/js/adminlte.min.js"></script>
</body>

</html>