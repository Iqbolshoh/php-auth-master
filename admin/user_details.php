<?php
session_start();

include '../config.php';
$query = new Database();
$query->check_session('admin');

if (isset($_GET['id'])) {
    $center_id = $_GET['id'];
    $center = $query->select('users', '*', 'role = ? AND id = ?', ['center', $center_id], 'si')[0];
}

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $query->delete("users", "id = ?", [$delete_id], 'i');
    header("Location: ./centers.php");
    exit;
}
?>

<?php include './header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h3>Center Details</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($center)): ?>
                    <div class="text-center mb-4">
                        <img src="<?= SITE_PATH . "/src/images/profile_picture/" . $center['profile_picture']; ?>"
                            alt="Profile Picture" class="rounded-circle" width="120" height="120">
                    </div>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td><?= htmlspecialchars($center['id']); ?></td>
                        </tr>
                        <tr>
                            <th>First Name</th>
                            <td><?= htmlspecialchars($center['first_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Last Name</th>
                            <td><?= htmlspecialchars($center['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($center['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?= htmlspecialchars($center['username']); ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><?= htmlspecialchars($center['role']); ?></td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= htmlspecialchars($center['created_at']); ?></td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td><?= htmlspecialchars($center['updated_at']); ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-between">
                        <form id="deleteForm" method="POST">
                            <input type="hidden" name="delete_id" value="<?= $center['id']; ?>">
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                        </form>

                        <a href="centers.php" class="btn btn-secondary">Back to List</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center">Center not found!</div>
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