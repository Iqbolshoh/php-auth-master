<?php
session_start();

include './config.php';
$query = new Database();
$query->check_session('user');

$user = $query->select("users", '*', "id = ?", [$_SESSION['user']['id']], 'i')[0];

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
        'last_name' => $last_name,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = $query->hashPassword($_POST['password']);
        $query->delete('active_sessions', 'user_id = ? AND session_token <> ?', [$_SESSION['user']['id'], session_id()], 'is');
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $encrypted_name = md5(bin2hex(random_bytes(32)) . '_' . bin2hex(random_bytes(16)) . '_' . uniqid('', true)) . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $targetFile = "./src/images/profile_picture/";

        $filePath = $targetFile . $user['profile_picture'];
        if (file_exists($filePath) && $user['profile_picture'] != 'default.png') {
            unlink($filePath);
        }

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile . $encrypted_name)) {
            $data['profile_picture'] = $encrypted_name;
            $_SESSION['user']['profile_picture'] = $encrypted_name;
        }
    }

    $update = $query->update("users", $data, "id = ?", [$_SESSION['user']['id']], "i");

    if ($update) {
        $_SESSION['user']['first_name'] = $first_name;
        $_SESSION['user']['last_name'] = $last_name;
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Success!', text: 'Your profile has been updated successfully!', timer: 1500, showConfirmButton: false }).then(() => { window.location.replace('profile.php'); }); };
        </script>
        <?php
    }
} elseif (isset($_POST['submit'])) {
    ?>
    <script>
        window.onload = function () { Swal.fire({ icon: 'error', title: 'Invalid CSRF Token', text: 'Please refresh the page and try again.', showConfirmButton: true }).then(() => { window.location.replace('profile.php'); });; };
    </script>
    <?php
}
?>

<?php include './header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-dark text-white text-center">
                <h4>Update Profile</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control"
                            value="<?php echo htmlspecialchars($user['first_name']); ?>" maxlength="30" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                            value="<?php echo htmlspecialchars($user['last_name']); ?>" maxlength="30" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>"
                            maxlength="100" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control"
                            value="<?php echo htmlspecialchars($user['username']); ?>" maxlength="30" disabled>
                    </div>
                    <div class="mb-3 position-relative">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" maxlength="255">
                            <button type="button" id="toggle-password" class="btn btn-outline-secondary">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small id="password-message" class="text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label fw-bold">Upload Image</label>
                        <div class="input-group">
                            <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                                style="display: none;">
                            <label for="profile_picture" style="background-color: white;
                                color: #007bff; 
                                border: 2px solid #007bff; 
                                border-radius: 5px; 
                                padding: 7px; 
                                cursor: pointer; 
                                transition: 0.3s; 
                                width: 100%; 
                                text-align: center; 
                                font-weight: bold; 
                                display: inline-block;">
                                📂 Upload Image
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= $query->generate_csrf_token() ?>">
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit" id="submit" class="btn btn-primary w-100">Update
                            Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

<?php include './footer.php'; ?>