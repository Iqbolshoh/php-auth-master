<?php
session_start();

include '../config.php';
$query = new Database();
$query->check_session('admin');

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $result = $query->select('users', '*', 'id = ?', [$user_id], 'i');
    if (!empty($result)) {
        $user = $result[0];
    }
}

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $query->delete("users", "id = ?", [$delete_id], 'i');
    header("Location: ./create_user.php");
    exit;
}

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
        $query->delete('active_sessions', 'user_id = ?', [$user_id], 'i');
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $encrypted_name = md5(bin2hex(random_bytes(32)) . '_' . bin2hex(random_bytes(16)) . '_' . uniqid('', true)) . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $targetFile = "../src/images/profile_picture/";

        $filePath = $targetFile . $user['profile_picture'];
        if (file_exists($filePath) && $user['profile_picture'] != 'default.png') {
            unlink($filePath);
        }

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile . $encrypted_name)) {
            $data['profile_picture'] = $encrypted_name;
        }
    }

    $update = $query->update("users", $data, "id = ?", [$user_id], "i");

    if ($update) {
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Success!', text: 'Your profile has been updated successfully!', timer: 1500, showConfirmButton: false }).then(() => { window.location.replace('user_details.php?id=' + <?= $user_id ?>); }); };
        </script>
        <?php
    }
} elseif (isset($_POST['submit'])) {
    ?>
    <script>
        window.onload = function () { Swal.fire({ icon: 'error', title: 'Invalid CSRF Token', text: 'Please refresh the page and try again.', showConfirmButton: true }).then(() => { window.location.replace('user_details.php?id=' + <?= $user_id ?>); });; };
    </script>
    <?php
}
?>

<?php include './header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg rounded-4 border-0">
            <div class="card-header bg-dark text-white text-center rounded-top-4">
                <h3 class="mb-0">User Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($user)): ?>
                    <div class="text-center mb-4">
                        <img src="<?= SITE_PATH . "/src/images/profile_picture/" . $user['profile_picture']; ?>"
                            alt="Profile Picture" class="rounded-circle border border-3 border-dark shadow-sm" width="140"
                            height="140" style="object-fit: cover; transition: 0.3s;"
                            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <h4 class="mt-3"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h4>
                        <p class="text-muted">@<?= htmlspecialchars($user['username']); ?></p>
                    </div>

                    <table class="table table-hover table-bordered rounded-3 overflow-hidden">
                        <tr>
                            <th class="bg-light">ID</th>
                            <td><?= htmlspecialchars($user['id']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">First Name</th>
                            <td><?= htmlspecialchars($user['first_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Last Name</th>
                            <td><?= htmlspecialchars($user['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Email</th>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Username</th>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Role</th>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($user['role']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Created At</th>
                            <td><?= htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Updated At</th>
                            <td><?= htmlspecialchars($user['updated_at']); ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-evenly mt-4">
                        <a href="create_user.php" class="btn btn-secondary flex-grow-1">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>

                        <button type="button" class="btn btn-warning flex-grow-1 mx-2" data-bs-toggle="modal"
                            data-bs-target="#editModal">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        <form id="deleteForm" method="POST" class="flex-grow-1">
                            <input type="hidden" name="delete_id" value="<?= $user['id']; ?>">
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <div class="alert alert-warning text-center rounded-3 shadow-sm">
                        <i class="fas fa-exclamation-circle"></i> User not found!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-dark text-white text-center rounded-top-4">
                    <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                    <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close"
                        style="background: transparent; border: none; font-size: 24px; font-weight: bold; color: white; cursor: pointer; line-height: 1;"
                        onmouseover="this.style.color='#ff4d4d'; this.style.transform='scale(1.2)'; this.style.transition='0.2s';"
                        onmouseout="this.style.color='white'; this.style.transform='scale(1)';">
                        ×
                    </button>
                </div>

                <div class="modal-body">
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
                                color: #495057; 
                                border: 2px solid #495057; 
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
                </div>

                <div class="modal-footer">
                    <button type="submit" name="submit" id="submit" class="btn btn-primary w-100">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm').submit();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Cancelled', 'The user is safe!', 'info');
            }
        });
    }
</script>

<?php include './footer.php'; ?>