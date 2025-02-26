<?php
session_start();

include './config.php';
$query = new Database();
$query->check_session('user');

$active_sessions = $query->select('active_sessions', '*', 'user_id = ?', [$_SESSION['user']['id']], 'i');

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'edit' && isset($_POST['device_name'])) {
        $device_name = trim($_POST['device_name']);
        if (empty($device_name)) {
            echo json_encode(["status" => "error", "message" => "Device name cannot be empty!"]);
            exit;
        }
        $query->update('active_sessions', ['device_name' => $device_name], 'session_token = ? AND user_id = ?', [session_id(), $_SESSION['user']['id']], 'si');
        echo json_encode(["status" => "success", "message" => "Device name updated!", "device_name" => $device_name]);
        exit;
    } elseif ($_POST['action'] === 'delete' && isset($_POST['token'])) {
        $deleted = $query->delete('active_sessions', 'user_id = ? AND session_token = ?', [$_SESSION['user']['id'], $_POST['token']], 'is');
        if ($deleted) {
            echo json_encode(["status" => "success", "message" => "Session deleted!", "token" => $_POST['token']]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete session. Try again!"]);
        }
        exit;
    }
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
            <tr id="session-<?= htmlspecialchars($session['session_token']); ?>" class="text-center">
                <td><?= $index + 1 ?></td>
                <td id="device-name-<?= htmlspecialchars($session['session_token']); ?>">
                    <?= htmlspecialchars($session['device_name']); ?>
                </td>
                <td><?= htmlspecialchars($session['ip_address']); ?></td>
                <td><?= date('H:i:s d-m-Y', strtotime($session['last_activity'])); ?></td>
                <td>
                    <?php if (session_id() == $session['session_token']): ?>
                        <button class="btn btn-warning btn-sm"
                            onclick="openEditModal('<?= htmlspecialchars($session['session_token']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-danger btn-sm"
                        onclick="confirmRemoval('<?= htmlspecialchars($session['session_token']); ?>')">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    function openEditModal(token) {
        let deviceName = document.getElementById(`device-name-${token}`).textContent.trim();
        Swal.fire({
            title: "Edit Device Name",
            input: "text",
            inputValue: deviceName,
            showCancelButton: true,
            confirmButtonText: "Save",
            preConfirm: (newName) => {
                if (!newName.trim()) {
                    Swal.showValidationMessage("Device name cannot be empty!");
                }
                return newName.trim();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append("action", "edit");
                formData.append("device_name", result.value);
                formData.append("csrf_token", "<?= $_SESSION['csrf_token'] ?>");

                fetch('', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            document.getElementById(`device-name-${token}`).textContent = data.device_name;
                            Swal.fire({ title: 'Success!', text: data.message, icon: 'success', showConfirmButton: false, timer: 1500 });
                        } else {
                            Swal.fire({ title: 'Error!', text: data.message, icon: 'error', showConfirmButton: true });
                        }
                    })
                    .catch(error => console.error("Fetch error:", error));
            }
        });
    }

    function confirmRemoval(token) {
        Swal.fire({
            title: "Are you sure?",
            text: "This cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, remove it!"
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append("action", "delete");
                formData.append("token", token);
                formData.append("csrf_token", "<?= $_SESSION['csrf_token'] ?>");

                fetch('', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            document.getElementById(`session-${data.token}`).remove();
                            Swal.fire({ title: 'Deleted!', text: data.message, icon: 'success', showConfirmButton: false, timer: 1500 }).then(() => {
                                if ("<?= session_id() ?>" == token) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire({ title: 'Error!', text: data.message, icon: 'error', showConfirmButton: true });
                        }
                    })
                    .catch(error => console.error("Fetch error:", error));
            }
        });
    }
</script>

<?php include './footer.php'; ?>