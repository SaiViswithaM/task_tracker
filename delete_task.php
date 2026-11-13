<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('error', 'Invalid task selected.');
    redirect('dashboard.php');
}
$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, current_user_id()]);
if ($stmt->rowCount() > 0) {
    set_flash('success', 'Task deleted successfully.');
} else {
    set_flash('error', 'Task not found or you do not have permission to delete it.');
}
redirect('dashboard.php');
