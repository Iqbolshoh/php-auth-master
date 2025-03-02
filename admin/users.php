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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        echo json_encode(['status' => 'error', 'title' => 'Invalid CSRF Token', 'message' => 'Invalid CSRF token!']);
        exit;
    }
    header('Content-Type: application/json');

    if ($_POST['action'] === 'create') {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim(strtolower($_POST['email']));
        $username = trim(strtolower($_POST['username']));
        $password = $_POST['password'];
        $role = trim($_POST['role']);

        if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'title' => 'Validation Error', 'message' => 'All fields are required!']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'title' => 'Email', 'message' => 'Invalid email format!']);
            exit;
        }

        if (!empty($query->select('users', 'email', 'email = ?', [$email], 's'))) {
            echo json_encode(['status' => 'error', 'title' => 'Email', 'message' => 'This email is already registered!']);
            exit;
        }

        if (strlen($username) < 3) {
            echo json_encode(['status' => 'error', 'title' => 'Username', 'message' => 'Username must be at least 3 characters long!']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            echo json_encode(['status' => 'error', 'title' => 'Username', 'message' => 'Username must be 3-30 characters: A-Z, a-z, 0-9, or _!']);
            exit;
        }

        if (!empty($query->select('users', 'username', 'username = ?', [$username], 's'))) {
            echo json_encode(['status' => 'error', 'title' => 'Username', 'message' => 'This username is already taken!']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['status' => 'error', 'title' => 'Password', 'message' => 'Password must be at least 8 characters long!']);
            exit;
        }
        $hashed_password = $query->hashPassword($password);

        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'username' => $username,
            'password' => $hashed_password,
            'role' => $role
        ];

        $result = $query->insert("users", $data);
        if ($result) {
            $data['id'] = $result;
            unset($data['password']);
            echo json_encode(['status' => 'success', 'title' => 'Success!', 'message' => 'New user added successfully!', 'user' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'title' => 'Error!', 'message' => 'Failed to add user.']);
        }
        exit;
    } elseif ($_POST['action'] === 'edit') {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        if (empty($first_name) || empty($last_name)) {
            echo json_encode(['status' => 'error', 'title' => 'Validation Error', 'message' => 'All fields are required!']);
            exit;
        }

        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                echo json_encode(['status' => 'error', 'title' => 'Password', 'message' => 'Password must be at least 8 characters long!']);
                exit;
            }
            $data['password'] = $query->hashPassword($_POST['password']);
            $query->delete('active_sessions', 'user_id = ?', [$user_id], 'i');
        }

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $encrypted_name = md5(bin2hex(random_bytes(32)) . '_' . date('Ymd_His') . '_' . uniqid('', true)) . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
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
        exit;
    } elseif ($_POST['action'] === 'delete') {
        if (isset($_POST['delete_id'])) {
            $delete_id = $_POST['delete_id'];
            $query->delete("users", "id = ?", [$delete_id], 'i');
            header("Location: ./users.php");
            exit;
        }
    }
}
$query->generate_csrf_token();
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
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

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
                            <input type="hidden" name="action" value="edit">
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

        document.getElementById('editProfileForm').addEventListener('submit', function (event) {
            event.preventDefault();

            let formData = new FormData(this);
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

<?php else: ?>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($user['role']); ?></span>
                                    </td>
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
                                    <option value="<?= htmlspecialchars($role) ?>">
                                        <?= ucfirst($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="action" value="create">
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        </div>
                        <div class="mb-3">
                            <button type="submit" name="submit" id="submit" class="btn btn-primary w-100">Add User</button>
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

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('signupForm');
            const fields = {
                email: document.getElementById('email'),
                username: document.getElementById('username'),
                password: document.getElementById('password')
            };
            const messages = {
                email: document.getElementById('email-message'),
                username: document.getElementById('username-message'),
                password: document.getElementById('password-message')
            };
            const submitBtn = document.getElementById('submit');
            const togglePassword = document.getElementById('toggle-password');

            let availability = { email: false, username: false };

            const validators = {
                email: email => /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email),
                username: username => /^[a-zA-Z0-9_]{3,30}$/.test(username),
                password: password => password.length >= 8
            };

            function checkAvailability(type, value) {
                if (!value) return;
                fetch('../signup/check_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `${type}=${encodeURIComponent(value)}`
                })
                    .then(res => res.json())
                    .then(data => {
                        messages[type].textContent = data.exists ? `This ${type} is already taken!` : '';
                        availability[type] = !data.exists;
                        updateSubmitState();
                    });
            }

            function updateSubmitState() {
                const validEmail = fields.email.value.length === 0 || validators.email(fields.email.value) && availability.email;
                const validUsername = fields.username.value.length === 0 || validators.username(fields.username.value) && availability.username;
                const validPassword = fields.password.value.length === 0 || validators.password(fields.password.value);

                const isValid = validEmail && validUsername && validPassword;
                submitBtn.disabled = !isValid;
                submitBtn.style.backgroundColor = isValid ? '#007bff' : '#b8daff';
                submitBtn.style.borderColor = isValid ? '#007bff' : '#b8daff';
                submitBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
            }

            Object.keys(fields).forEach(type => {
                fields[type].addEventListener('input', function () {
                    if (!validators[type](this.value)) {
                        messages[type].textContent = type === 'password' ? 'Password must be at least 8 characters long!' : `Invalid ${type} format!`;
                        availability[type] = false;
                        updateSubmitState();
                        return;
                    }
                    messages[type].textContent = '';
                    if (type !== 'password') checkAvailability(type, this.value);
                    updateSubmitState();
                });
            });

            togglePassword.addEventListener('click', function () {
                fields.password.type = fields.password.type === 'password' ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                try {
                    const response = await fetch('', { method: 'POST', body: new FormData(form) });
                    const data = await response.json();

                    await Swal.fire({
                        icon: data.status === 'success' ? 'success' : 'error',
                        title: data.status === 'success' ? 'Registration successful' : data.title,
                        text: data.message,
                        timer: data.status === 'success' ? 1500 : undefined,
                        showConfirmButton: data.status !== 'success'
                    });

                    console.log(data)
                    if (data.status === 'success') {
                        let newRow = `
                            <tr>
                                <td>${data.user.id}</td>
                                <td>${data.user.first_name} ${data.user.last_name}</td>
                                <td>${data.user.username}</td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">${data.user.role}</span>
                                </td>
                                <td>
                                    <a href="users.php?id=${data.user.id}" class="btn btn-warning btn-sm">Details</a>
                                </td>
                            </tr>`;

                        document.querySelector('#usersTable tbody').insertAdjacentHTML('beforeend', newRow);
                        document.getElementById('signupForm').reset();
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong! Try again.' });
                }
            });
        });
    </script>

<?php endif; ?>

<?php include './footer.php'; ?>