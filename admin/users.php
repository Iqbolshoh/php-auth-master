<?php
session_start();

include '../config.php';
$query = new Database();
$query->check_session('admin');

$users = $query->select('users', '*', 'id <> ?', [$_SESSION['user']['id']], 's');

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $user = $query->select('users', '*', 'id = ?', [$user_id], 'i')[0] ?? null;
}

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $query->delete("users", "id = ?", [$delete_id], 'i');
    header("Location: ./users.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST['csrf_token']) &&
        isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        header('Content-Type: application/json');

        if ($_POST['action'] === 'create') {
            $first_name = $query->validate($_POST['first_name']);
            $last_name = $query->validate($_POST['last_name']);
            $email = $query->validate($_POST['email']);
            $username = $query->validate($_POST['username']);
            $password = $query->hashPassword($_POST['password']);
            $role = $query->validate($_POST['role']);

            $data = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'username' => $username,
                'password' => $password,
                'role' => $role
            ];

            $result = $query->insert("users", $data);
            if ($result) {
                $data['id'] = $result;
                echo json_encode(['status' => 'success', 'title' => 'Success!', 'message' => 'New user added successfully!', 'user' => $data]);
            } else {
                echo json_encode(['status' => 'error', 'title' => 'Error!', 'message' => 'Failed to add user.']);
            }
        } elseif ($_POST['action'] === 'edit') {

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
                echo json_encode(['status' => 'success', 'title' => 'Success!', 'message' => 'Profile updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'title' => 'Error!', 'message' => 'Failed to update profile!']);
            }
        }

    } else {
        echo json_encode(['status' => 'error', 'title' => 'Invalid CSRF Token', 'message' => 'Please refresh the page and try again.']);
    }
    exit;
}
?>

<?php include './header.php'; ?>

<?php if (!empty($user)): ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-4 border-0">
                <div class="card-header bg-dark text-white text-center rounded-top-4">
                    <h3 class="mb-0">User Details</h3>
                </div>
                <div class="card-body">

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
                            <td><?= date('H:i:s d-m-Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Updated At</th>
                            <td><?= date('H:i:s d-m-Y', strtotime($user['updated_at'])); ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-evenly mt-4">
                        <a href="users.php" class="btn btn-secondary flex-grow-1">
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

                </div>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">Users List</div>
                <div class="card-body">
                    <table id="usersTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td>
                                        <a href="users.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">Add New User</div>
                <div class="card-body">
                    <form id="signupForm" method="POST">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name_input" name="first_name" class="form-control" maxlength="30"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name_input" name="last_name" class="form-control" maxlength="30"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required maxlength="100">
                            <small id="email-message" class="text-danger"></small>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required maxlength="30">
                            <small id="username-message" class="text-danger"></small>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" required
                                    maxlength="255">
                                <button type="button" id="toggle-password" class="btn btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small id="password-message" class="text-danger"></small>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="" disabled selected>-- Select Role --</option>
                                <?php foreach (ROLES as $role => $path): ?>
                                    <option value="<?= htmlspecialchars($role) ?>"><?= ucfirst($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        </div>
                        <div class="mb-3">
                            <button type="submit" name="submit" id="submit" class="btn btn-primary w-100">
                                Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthMenu": [5, 10, 25, 50, 100],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const emailField = document.getElementById('email');
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            const emailMessage = document.getElementById('email-message');
            const usernameMessage = document.getElementById('username-message');
            const passwordMessage = document.getElementById('password-message');
            const submitButton = document.getElementById('submit');
            const togglePassword = document.getElementById('toggle-password');

            let emailAvailable = false;
            let usernameAvailable = false;

            function validateEmailFormat(email) {
                return /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email);
            }

            function validateUsernameFormat(username) {
                return /^[a-zA-Z0-9_]{3,30}$/.test(username);
            }

            function validatePassword() {
                if (passwordField.value.length < 8) {
                    passwordMessage.textContent = 'Password must be at least 8 characters long!';
                    return false;
                }
                passwordMessage.textContent = '';
                return true;
            }

            function checkAvailability(type, value, messageElement, callback) {
                if (!value) return;

                fetch('../signup/check_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `${type}=${encodeURIComponent(value)}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            messageElement.textContent = `This ${type} is already taken!`;
                            callback(false);
                        } else {
                            messageElement.textContent = '';
                            callback(true);
                        }
                        updateSubmitButtonState();
                    });
            }

            function updateSubmitButtonState() {
                const isEmailValid = emailField.value.length === 0 || (validateEmailFormat(emailField.value) && emailAvailable);
                const isUsernameValid = usernameField.value.length === 0 || (validateUsernameFormat(usernameField.value) && usernameAvailable);
                const isPasswordValid = passwordField.value.length === 0 || validatePassword();

                const isFormValid = isEmailValid && isUsernameValid && isPasswordValid;

                submitButton.disabled = !isFormValid;
                submitButton.style.backgroundColor = isFormValid ? '#007bff' : '#b8daff';
                submitButton.style.borderColor = isFormValid ? '#007bff' : '#b8daff';
                submitButton.style.cursor = isFormValid ? 'pointer' : 'not-allowed';
            }

            emailField.addEventListener('input', function () {
                if (!validateEmailFormat(this.value)) {
                    emailMessage.textContent = 'Invalid email format!';
                    emailAvailable = false;
                    updateSubmitButtonState();
                    return;
                }
                checkAvailability('email', this.value, emailMessage, status => {
                    emailAvailable = status;
                });
            });

            usernameField.addEventListener('input', function () {
                if (!validateUsernameFormat(this.value)) {
                    usernameMessage.textContent = 'Username must be 3-30 characters: A-Z, a-z, 0-9, or _.';
                    usernameAvailable = false;
                    updateSubmitButtonState();
                    return;
                }
                checkAvailability('username', this.value, usernameMessage, status => {
                    usernameAvailable = status;
                });
            });

            passwordField.addEventListener('input', function () {
                validatePassword();
                updateSubmitButtonState();
            });

            togglePassword.addEventListener('click', function () {
                passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('signupForm').addEventListener('submit', function (event) {
                event.preventDefault();

                let formData = new FormData(this);
                formData.append('action', 'create')

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({ icon: "success", title: data.title, text: data.message, timer: 1500, showConfirmButton: false })

                            let newRow = `
                            <tr>
                                <td>${data.user.id}</td>
                                <td>${data.user.first_name} ${data.user.last_name}</td>
                                <td>${data.user.username}</td>
                                <td>${data.user.role}</td>
                                <td>
                                    <a href="users.php?id=${data.user.id}" class="btn btn-warning btn-sm">Details</a>
                                </td>
                            </tr>`;

                            document.querySelector('#usersTable tbody').insertAdjacentHTML('beforeend', newRow);
                            document.getElementById('signupForm').reset();
                        } else {
                            Swal.fire({ icon: "error", title: data.title, text: data.message, showConfirmButton: true });
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

    </script>

<?php endif; ?>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" id="editProfileForm">
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
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="submit" id="submit" class="btn btn-primary w-100">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        let submitBtn = document.getElementById('submit')
        let isDisabled = this.value.length < 8;
        submitBtn.disabled = isDisabled;
        submitBtn.style.backgroundColor = !isDisabled ? '#007bff' : '#b8daff';
        submitBtn.style.borderColor = !isDisabled ? '#007bff' : '#b8daff';
        submitBtn.style.cursor = !isDisabled ? 'pointer' : 'not-allowed';
        passwordMessage.textContent = isDisabled ? 'Password must be at least 8 characters long!' : '';
    });

    document.getElementById("editProfileForm").addEventListener("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);
        formData.append('action', 'edit')

        fetch('', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: data.title, text: data.message, timer: 1500, showConfirmButton: false }).then(() => { window.location.reload(); })
                } else {
                    Swal.fire({ icon: 'error', title: data.title, text: data.message, showConfirmButton: true });
                }
            })
            .catch(error => console.error('Error:', error));
    });

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