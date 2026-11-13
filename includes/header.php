<?php
$pageTitle = $pageTitle ?? 'Task Tracker';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Task Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= !empty($_SESSION['user_id']) ? 'dashboard.php' : 'index.php' ?>">
        <span class="brand-mark">✓</span> Task Tracker
    </a>
    <nav>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span class="nav-user">Hi, <?= e($_SESSION['user_name'] ?? 'User') ?></span>
            <a href="dashboard.php">Dashboard</a>
            <form class="nav-form" action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button class="nav-link" type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a class="nav-cta" href="register.php">Create account</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
    <?php endif; ?>
