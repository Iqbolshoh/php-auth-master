<?php
session_start();

include '../config.php';
$query = new Database();
$query->check_session('admin');

$users = $query->select('users', '*', 'id <> ?', [$_SESSION['user']['id']], 's');

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['csrf_token']) &&
    isset($_SESSION['csrf_token']) &&
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {

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
        echo json_encode(['status' => 'success', 'message' => 'New user added successfully!', 'user' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add user.']);
    }
    exit;
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #email-message,
    #username-message,
    #password-message {
        color: red;
        font-size: 14px;
        margin-top: 5px;
    }

    .password-container {
        position: relative;
        display: flex;
        align-items: user;
    }

    .password-container input {
        flex: 1;
        padding-right: 40px;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        cursor: pointer;
        border: none;
        background: transparent;
    }
</style>

<?php include './header.php'; ?>

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
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td>
                                    <a href="user_details.php?id=<?= $user['id'] ?>"
                                        class="btn btn-warning btn-sm">Details</a>
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
                        <input type="email" id="email" name="email" class="form-control" maxlength="100" required>
                        <small id="email-message"></small>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" maxlength="30" required>
                        <small id="username-message"></small>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" class="form-control" maxlength="255"
                                required>
                            <button type="button" id="toggle-password" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small id="password-message"></small>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="" disabled selected>-- Select Role --</option>
                            <?php foreach (ROLES as $role => $path): ?>
                                <option value="<?= htmlspecialchars($role) ?>"><?= ucfirst($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= $query->generate_csrf_token() ?>">
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
                passwordMessage.textContent = 'Min 8 characters required.';
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

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ icon: "success", title: "Success!", text: data.message, timer: 1500, showConfirmButton: false })

                        let newRow = `
                    <tr>
                        <td>${data.user.id}</td>
                        <td>${data.user.first_name} ${data.user.last_name}</td>
                        <td>${data.user.username}</td>
                        <td>${data.user.role}</td>
                        <td>
                            <a href="user_details.php?id=${data.user.id}" class="btn btn-warning btn-sm">Details</a>
                        </td>
                    </tr>`;

                        document.querySelector('#usersTable tbody').insertAdjacentHTML('beforeend', newRow);
                        document.getElementById('signupForm').reset();
                    } else {
                        Swal.fire({ icon: "error", title: "Error!", text: data.message, showConfirmButton: true });
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

</script>

<?php include './footer.php'; ?>