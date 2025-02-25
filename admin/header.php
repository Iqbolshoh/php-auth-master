<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$menuItems = [
    [
        "menuTitle" => "Dashboard",
        "icon" => "fas fa-tachometer-alt",
        "pages" => [
            ["title" => "Home", "url" => "index.php"]
        ],
    ],
    [
        "menuTitle" => "Settings",
        "icon" => "fas fa-cog",
        "pages" => [
            ["title" => "Update Profile", "url" => "profile.php"],
            ["title" => "Active Sessions", "url" => "active_sessions.php"]
        ],
    ]
];

$active_pageInfo = null;
foreach ($menuItems as $menuItem) {
    foreach ($menuItem['pages'] as $page) {
        if ($currentPage === $page['url']) {
            $active_pageInfo = [
                "breadcrumb_Items" => [
                    ["title" => $menuItem['menuTitle'], "url" => "#"],
                    ["title" => $page['title'], "url" => $page['url']]
                ],
                "page_title" => $page['title'],
                "active_menu" => $menuItem,
                "active_page" => $page
            ];
            break 2;
        }
    }
}

$breadcrumb_Items = $active_pageInfo['breadcrumb_Items'] ?? [];
$page_title = $active_pageInfo['page_title'] ?? '';
$active_menu = $active_pageInfo['active_menu'] ?? null;
$active_page = $active_pageInfo['active_page'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Powerful admin panel by Iqbolshoh Ilhomjonov">
    <meta name="keywords"
        content="iqbolshoh, iqbolshoh_777, iqbolshoh_dev, iqbolshoh.uz, <?= htmlspecialchars($page_title) . ', ' . htmlspecialchars(SITE_PATH) ?>">
    <meta name="author" content="iqbolshoh.uz">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph (OG) tags -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="Powerful admin panel by Iqbolshoh Ilhomjonov">
    <meta property="og:image" content="<?= SITE_PATH ?>/src/images/logo.png">
    <meta property="og:url" content="<?= SITE_PATH ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Iqbolshoh Admin Panel">

    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" href="<?= SITE_PATH . "/favicon.ico" ?>" type="image/x-icon">

    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_PATH ?>/src/css/adminlte.min.css">

    <!-- JS -->
    <script src="<?= SITE_PATH ?>/src/js/jquery.min.js"></script>
    <script src="<?= SITE_PATH ?>/src/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="<?= SITE_PATH ?>/src/js/adminlte.min.js" defer></script>
</head>

<body class="hold-transition sidebar-mini">
    <!-- Body started -->
    <div class="wrapper">
        <!-- Wrapper started -->

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= SITE_PATH ?>" class="nav-link">Home</a>
                </li>
            </ul>
            <form class="form-inline ml-3">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" placeholder="Search" name="search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#messages">
                        <i class="far fa-comments"></i>
                        <span class="badge badge-danger navbar-badge">2</span>
                    </a>
                </li>
                <li class="nav-item dropdown"><a class="nav-link" href="#notifications">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">5</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="main-header" style="padding: 0px 10px; background-color: #f4f6f9; border-bottom: none !important;">
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><?= $page_title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <?php foreach ($breadcrumb_Items as $item): ?>
                                <li class="breadcrumb-item <?= $item['url'] === '#' ? 'active' : '' ?>">
                                    <?= $item['url'] === '#' ? $item['title'] : "<a href='{$item['url']}'>{$item['title']}</a>" ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="<?= SITE_PATH ?>" class="brand-link">
                <img src="<?= SITE_PATH ?>/src/images/logo.svg" alt="Logo" class="brand-image img-circle bg-white">
                <span class="brand-text font-weight-light">
                    <?= ucfirst(string: $_SESSION['user']['role']) ?> Panel
                </span>
            </a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <?php $image_path = SITE_PATH . "/src/images/profile_picture/" . $_SESSION['user']['profile_picture'] ?>
                        <img src="<?= $image_path ?>" class="img-circle elevation-2 bg-white" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="<?= SITE_PATH ?>" class="d-block">
                            <?= $_SESSION['user']['first_name'] . " " . $_SESSION['user']['last_name'] ?>
                        </a>
                    </div>
                </div>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <?php foreach ($menuItems as $menuItem): ?>
                            <li class="nav-item has-treeview <?= $menuItem === $active_menu ? 'menu-open' : '' ?>">
                                <a class="nav-link <?= $menuItem === $active_menu ? 'active' : '' ?>" href="#">
                                    <i class="nav-icon <?= $menuItem['icon'] ?>"></i>
                                    <p>
                                        <?= $menuItem['menuTitle'] ?>
                                        <?= !empty($menuItem['pages']) ? '<i class="right fas fa-angle-left"></i>' : '' ?>
                                    </p>
                                </a>
                                <?php if (!empty($menuItem['pages'])): ?>
                                    <ul class="nav nav-treeview">
                                        <?php foreach ($menuItem['pages'] as $page): ?>
                                            <li class="nav-item">
                                                <a href="<?= $page['url'] ?>"
                                                    class="nav-link <?= $page === $active_page ? 'active' : '' ?>">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p><?= $page['title'] ?></p>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        <li class="nav-item" onclick="logout()">
                            <a href="javascript:void(0);" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <!-- Content-wrapper started -->
            <section class="content">
                <!-- Content section started -->
                <div class="container-fluid">
                    <!-- Container-fluid started -->