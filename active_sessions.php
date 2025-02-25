<?php
session_start();

include './config.php';
$query = new Database();
$query->check_session('user');

$active_sessions = $query->select('active_sessions', '*', 'user_id = ?', [$_SESSION['user']['id']], 'i');

if (isset($_GET['token'])) {
    $query->delete('active_sessions', 'user_id = ? AND session_token = ?', [$_SESSION['user']['id'], $_GET['token']], 'is');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['update_session'])) {
    $device_name = $_POST['device_name'];

    $query->update(
        'active_sessions',
        ['device_name' => $device_name],
        'session_token = ? AND user_id = ?',
        [session_id(), $_SESSION['user']['id']],
        'si'
    );

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php include './header.php'; ?>

<table class="table table-striped table-hover table-bordered">
    <thead class="bg-dark text-white text-center">
        <tr>
            <th> №</th>
            <th><i class="fas fa-desktop"></i> Device Name</th>
            <th><i class="fas fa-network-wired"></i> IP Address</th>
            <th><i class="fas fa-clock"></i> Last Activity</th>
            <th><i class="fas fa-cog"></i> Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($active_sessions as $index => $session): ?>
            <tr id="session-<?php echo htmlspecialchars($session['session_token']); ?>" class="text-center">
                <td><?= $index + 1 ?></td>
                <td class="device-name"> <?php echo htmlspecialchars($session['device_name']); ?></td>
                <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
                <td><?php echo date('H:i:s d-m-Y', strtotime($session['last_activity'])); ?></td>
                <td class="text-center">
                    <?php if (session_id() == $session['session_token']): ?>
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditModal('<?php echo htmlspecialchars($session['device_name']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    <?php endif ?>
                    <button class="btn btn-danger btn-sm"
                        onclick="confirmRemoval('<?php echo htmlspecialchars($session['session_token']); ?>')">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Device Name</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="deviceName">Device Name</label>
                        <input type="text" class="form-control" name="device_name" id="deviceName" required>
                    </div>
                    <button type="submit" name="update_session" class="btn btn-primary">
                        Save changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditModal(deviceName) {
        document.getElementById('deviceName').value = deviceName;
        $('#editModal').modal('show');
    }

    function confirmRemoval(token) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'active_sessions.php?token=' + token;
            }
        });
    }
</script>

<?php include './footer.php'; ?>