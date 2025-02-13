<?php
session_start();
include './config.php';
$query = new Database();
$query->checkUserSession('user');

// Foydalanuvchi ma'lumotlarini olish
$user = $query->select("users", '*', "id = ?", [$_SESSION['user_id']], 'i');
$user = $user ? $user[0] : null;

// Ma'lumotlarni yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];

    $query->update(
        "users",
        [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'username' => $username
        ],
        "id = ?",
        [$_SESSION['user_id']],
        "i"
    );
    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include './header.php'; ?>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">Your profile has been updated successfully!</div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control"
                                value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control"
                                value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control"
                                value="<?php echo htmlspecialchars($user['username']); ?>" required disabled>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </section>

        </div>
        <?php include './footer.php'; ?>
    </div>
    <script src="./src/js/jquery.min.js"></script>
    <script src="./src/js/bootstrap.bundle.min.js"></script>
    <script src="./src/js/adminlte.min.js"></script>
</body>

</html>