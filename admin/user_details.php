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
        <form method="POST" action="update_user.php">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-warning text-dark rounded-top-4">
                    <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">

                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name"
                            value="<?= htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name"
                            value="<?= htmlspecialchars($user['last_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email"
                            value="<?= htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username"
                            value="<?= htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="" disabled selected>-- Select Role --</option>
                            <?php foreach (ROLES as $role => $path): ?>
                                <option value="<?= htmlspecialchars($role) ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Save Changes</button>
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