<?php
session_start();

if (($_SESSION['loggedin'] ?? false) !== true || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ./login/");
    exit;
}

include './config.php';
$query = new Database();
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

                    <h1>Active Sessions</h1>
                    <table>
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>IP Address</th>
                                <th>Last Activity</th>
                                <th>Action</th>
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
                                echo "<td><a href='remove_session.php?token={$session['session_token']}'>Remove</a></td>";
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

    <!-- jQuery -->
    <script src="./src/js/jquery.min.js"></script>

    <!-- Bootstrap 4 -->
    <script src="./src/js/bootstrap.bundle.min.js"></script>
    <script src="./src/js/adminlte.min.js"></script>
</body>

</html>