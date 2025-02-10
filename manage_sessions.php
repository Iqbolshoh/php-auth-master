<!-- manage_sessions.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sessions</title>
</head>

<body>
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
</body>

</html>