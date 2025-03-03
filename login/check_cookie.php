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

    $patterns = [
        // Windows versions (extracting the version dynamically)
        'Windows NT ([0-9.]+)' => 'Windows',

        // Apple devices (extracting the version for iPhone, iPad, and Mac)
        'iPhone OS ([0-9_]+)' => 'iPhone',
        'iPad; CPU OS ([0-9_]+)' => 'iPad',
        'Mac OS X ([0-9_]+)' => 'Mac',

        // Android version extraction
        'Android ([0-9.]+)' => 'Android',

        // Linux distributions (some common ones)
        'Ubuntu' => 'Ubuntu',
        'Fedora' => 'Fedora',
        'Debian' => 'Debian',
        'Arch' => 'Arch',
        'Mint' => 'Mint',
        'Red Hat' => 'Red Hat',
        'openSUSE' => 'openSUSE',
        'CentOS' => 'CentOS',
        'Gentoo' => 'Gentoo',
        'Slackware' => 'Slackware',
        'Linux' => 'Linux',

        // Popular smartphone brands (extracting model numbers where possible)
        'Samsung SM-([A-Za-z0-9]+)' => 'Samsung',
        'Huawei ([A-Za-z0-9]+)' => 'Huawei',
        'Redmi ([A-Za-z0-9]+)' => 'Redmi',
        'Mi ([A-Za-z0-9]+)' => 'Xiaomi Mi',
        'Poco ([A-Za-z0-9]+)' => 'Poco',
        'OnePlus ([A-Za-z0-9]+)' => 'OnePlus',
        'Oppo ([A-Za-z0-9]+)' => 'Oppo',
        'Vivo ([A-Za-z0-9]+)' => 'Vivo',
        'Realme ([A-Za-z0-9]+)' => 'Realme',
        'Sony ([A-Za-z0-9]+)' => 'Sony Xperia',
        'Nokia ([A-Za-z0-9]+)' => 'Nokia',
        'Motorola ([A-Za-z0-9]+)' => 'Motorola',
        'LG ([A-Za-z0-9]+)' => 'LG',
        'Asus ([A-Za-z0-9]+)' => 'Asus',

        // Tablet devices
        'Lenovo TB-([A-Za-z0-9]+)' => 'Lenovo Tablet',
        'Samsung SM-T([A-Za-z0-9]+)' => 'Samsung Tablet',
        'iPad' => 'iPad',
    ];

    foreach ($patterns as $pattern => $device) {
        if (preg_match('/' . $pattern . '/i', $user_agent, $matches)) {
            $version = isset($matches[1]) ? ' ' . str_replace('_', '.', $matches[1]) : '';
            return $device . $version;
        }
    }

    return 'Unknown';
}
