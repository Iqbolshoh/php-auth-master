<?php
session_start();

include './config.php';
$query = new Database();
$query->check_session('user');

$active_sessions = $query->select('active_sessions', '*', 'user_id = ?', [$_SESSION['user']['id']], 'i');

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['submit']) &&
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    isset($_POST['action']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {

    if ($_POST['action'] === 'edit' && isset($_POST['device_name'])) {
        $device_name = $_POST['device_name'];

        $query->update(
            'active_sessions',
            ['device_name' => $device_name],
            'session_token = ? AND user_id = ?',
            [session_id(), $_SESSION['user']['id']],
            'si'
        );
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Device Name Updated!', text: 'Your device name has been successfully changed.', timer: 1500, showConfirmButton: false }).then(() => { window.location.replace('active_sessions.php'); }); };
        </script>
        <?php
    } elseif ($_POST['action'] === 'delete' && isset($_POST['token'])) {
        $query->delete('active_sessions', 'user_id = ? AND session_token = ?', [$_SESSION['user']['id'], $_POST['token']], 'is');
        ?>
        <script>
            window.onload = function () { Swal.fire({ icon: 'success', title: 'Session Deleted!', text: 'The selected session has been successfully removed.', timer: 1500, showConfirmButton: false }).then(() => { window.location.replace('active_sessions.php'); }); };
        </script>
        <?php
    }
} elseif (isset($_POST['submit'])) {
    ?>
    <script>
        window.onload = function () { Swal.fire({ icon: 'error', title: 'Invalid CSRF Token', text: 'Please refresh the page and try again.', showConfirmButton: true }).then(() => { window.location.replace('./'); });; };
    </script>
    <?php
}
?>

<?php include './header.php'; ?>

<table class="table table-striped table-hover table-bordered">
    <thead class="bg-dark text-white text-center">
        <tr>
            <th>№</th>
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
                    <input type="hidden" name="action" value="edit">
                    <div class="form-group">
                        <label for="deviceName">Device Name</label>
                        <input type="text" class="form-control" name="device_name" id="deviceName" required>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="csrf_token" value="<?= $query->generate_csrf_token() ?>">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">
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
</script>

<?php include './footer.php'; ?>