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
    <div class="col-md-10">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h3>User Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($user)): ?>
                    <div class="text-center mb-4">
                        <img src="<?= SITE_PATH . "/src/images/profile_picture/" . $user['profile_picture']; ?>"
                            alt="Profile Picture" class="rounded-circle" width="120" height="120">
                    </div>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td><?= htmlspecialchars($user['id']); ?></td>
                        </tr>
                        <tr>
                            <th>First Name</th>
                            <td><?= htmlspecialchars($user['first_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Last Name</th>
                            <td><?= htmlspecialchars($user['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td><?= htmlspecialchars($user['updated_at']); ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-between">
                        <form id="deleteForm" method="POST">
                            <input type="hidden" name="delete_id" value="<?= $user['id']; ?>">
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                        </form>

                        <a href="create_user.php" class="btn btn-secondary">Back to List</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center">User not found!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
                Swal.fire('Cancelled', 'The center is safe!', 'info');
            }
        });
    }
</script>

<?php include './footer.php'; ?>