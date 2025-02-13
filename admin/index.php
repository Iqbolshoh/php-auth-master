<?php
session_start();

include '../config.php';
$query = new Database();
$query->checkUserSession('admin');

$user = $query->select("users", '*', "id = ?", [$_SESSION['user_id']], 'i')[0] ?? null;

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['submit']) &&
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $first_name = $query->validate($_POST['first_name']);
    $last_name = $query->validate($_POST['last_name']);

    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = $query->hashPassword($_POST['password']);
        $query->delete('active_sessions', 'user_id = ? AND session_token <> ?', [$_SESSION['user_id'], session_id()], 'is');
    }

    $update = $query->update("users", $data, "id = ?", [$_SESSION['user_id']], "i");

    if ($update) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Success!', text: 'Your profile has been updated successfully!', timer: 1500, showConfirmButton: false }).then(() => { window.location.href = 'index.php'; }); };
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="../src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include './header.php'; ?>
        <div class="content-wrapper">

            <section class="content">
                <div class="container-fluid">

                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card shadow-lg">
                                <div class="card-header bg-dark text-white text-center">
                                    <h4>Update Profile</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" name="first_name" class="form-control"
                                                value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" name="last_name" class="form-control"
                                                value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control"
                                                value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control"
                                                value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                        </div>
                                        <div class="mb-3 position-relative">
                                            <label class="form-label">Password</label>
                                            <div class="input-group">
                                                <input type="password" id="password" name="password"
                                                    class="form-control" maxlength="255">
                                                <button type="button" id="toggle-password"
                                                    class="btn btn-outline-secondary">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <small id="password-message" class="text-danger"></small>
                                        </div>
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                        <div class="d-grid">
                                            <button type="submit" name="submit" id="submit"
                                                class="btn btn-primary w-100">Update
                                                Profile</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

        </div>
        <?php include './footer.php'; ?>
    </div>
    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const toggleIcon = this.querySelector('i');
            passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye');
            toggleIcon.classList.toggle('fa-eye-slash');
        });

        document.getElementById('password').addEventListener('input', function () {
            const passwordMessage = document.getElementById('password-message');
            document.getElementById('submit').disabled = this.value.length < 8;
            passwordMessage.textContent = this.value.length < 8 ? 'Password must be at least 8 characters long!' : '';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../src/js/jquery.min.js"></script>
    <script src="../src/js/bootstrap.bundle.min.js"></script>
    <script src="../src/js/adminlte.min.js"></script>

</body>

</html>