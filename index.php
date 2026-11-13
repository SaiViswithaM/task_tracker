<?php
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}
redirect('login.php');
