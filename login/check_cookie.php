<?php
session_start();

include '../config.php';
$query = new Database();

if (!empty($_SESSION['loggedin']) && isset(ROLES[$_SESSION['user']['role']])) {
    header("Location: " . SITE_PATH . ROLES[$_SESSION['user']['role']]);
    exit;
}

if (!empty($_COOKIE['username']) && !empty($_COOKIE['session_token']) && session_id() !== $_COOKIE['session_token']) {
    session_write_close();
    session_id($_COOKIE['session_token']);
    session_start();
}

if (!empty($_COOKIE['username'])) {
    $username = $_COOKIE['username'];
    $user = $query->select('users', '*', 'username = ?', [$username], 's')[0] ?? null;

    if (!empty($user)) {
        unset($user['password']);
        $_SESSION['loggedin'] = true;
        $_SESSION['user'] = $user;

        $active_session = $query->select('active_sessions', '*', 'session_token = ?', [session_id()], 's');

        if (!empty($active_session)) {
            $query->update(
                'active_sessions',
                ['last_activity' => date('Y-m-d H:i:s')],
                'session_token = ?',
                [session_id()],
                's'
            );
        }

        if (isset(ROLES[$_SESSION['user']['role']])) {
            header('Location: ' . SITE_PATH . ROLES[$_SESSION['user']['role']]);
            exit;
        }
    }
}

function get_device_name()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $devices = [
        // Apple
        '/iPhone/' => 'iPhone',
        '/iPad/' => 'iPad',
        '/Mac/' => 'Mac',

        // Windows
        '/Windows NT 10/' => 'Windows 10',
        '/Windows NT 6.3/' => 'Windows 8.1',
        '/Windows NT 6.2/' => 'Windows 8',
        '/Windows NT 6.1/' => 'Windows 7',
        '/Windows NT 6.0/' => 'Windows Vista',
        '/Windows NT 5.1/' => 'Windows XP',

        // Android
        '/Android/' => 'Android',

        // Linux
        '/Ubuntu/' => 'Ubuntu',
        '/Fedora/' => 'Fedora',
        '/Debian/' => 'Debian',
        '/Arch/' => 'Arch',
        '/Linux/' => 'Linux',

        // Browsers
        '/Chrome/' => 'Chrome',
        '/Firefox/' => 'Firefox',
        '/Edge/' => 'Edge',
        '/Safari/' => 'Safari'
    ];

    foreach ($devices as $pattern => $device) {
        if (preg_match($pattern, $user_agent)) {
            return $device;
        }
    }

    return 'Unknown';
}
