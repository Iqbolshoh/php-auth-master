<?php
session_start();

include './config.php';
$query = new Database();
$query->checkUserSession('user');

if (isset($_GET['token'])) {
    $query->delete('active_sessions', 'session_token = ?', [$_GET['token']], 's');
    header('Location: active_sessions.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include './header.php' ?>
        <div class="content-wrapper">

            <section class="content">
                <div class="container-fluid">

                    <table class="">
                        <thead class="">
                            <tr>
                                <th><i class="fas fa-desktop"></i> Device Name</th>
                                <th><i class="fas fa-network-wired"></i> IP Address</th>
                                <th><i class="fas fa-clock"></i> Last Activity</th>
                                <th><i class="fas fa-cog"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sessions = $query->select('active_sessions', '*', 'user_id = ?', [$_SESSION['user_id']], 'i');
                            foreach ($sessions as $session) {
                                echo "<tr>";
                                echo "<td>{$session['device_name']}</td>";
                                echo "<td>{$session['ip_address']}</td>";
                                echo "<td>{$session['last_activity']}</td>";
                                echo "<td>
                    <button class='btn btn-danger btn-sm' onclick='confirmRemoval(\"{$session['session_token']}\")'>
                        <i class='fas fa-trash-alt'></i> Remove
                    </button>
                  </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                </div>
            </section>

        </div>
        <?php include './footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./src/js/jquery.min.js"></script>
    <script src="./src/js/bootstrap.bundle.min.js"></script>
    <script src="./src/js/adminlte.min.js"></script>
    <script>
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
</body>

</html>