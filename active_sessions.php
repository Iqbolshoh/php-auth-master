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

                    <table class="table table-hover table-bordered text-center align-middle">
                        <thead class="table-dark">
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
                                echo "<td><span class='badge bg-primary'>{$session['device_name']}</span></td>";
                                echo "<td><span class='badge bg-info'>{$session['ip_address']}</span></td>";
                                echo "<td><span class='badge bg-success'>{$session['last_activity']}</span></td>";
                                echo "<td>
                    <button class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#removeSessionModal' data-token='{$session['session_token']}'>
                        <i class='fas fa-trash-alt'></i> Remove
                    </button>
                  </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <!-- Modal for Confirm Delete -->
                    <div class="modal fade" id="removeSessionModal" tabindex="-1" aria-labelledby="removeSessionLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="removeSessionLabel"><i
                                            class="fas fa-exclamation-triangle"></i> Confirm Removal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to remove this session?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <a id="confirmDelete" href="#" class="btn btn-danger">Remove</a>
                                </div>

                            </div>
                        </div>
                    </div>

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