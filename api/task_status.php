<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
$userId = api_require_login();
api_verify_csrf();
$data = api_input();

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$status = (string)($data['status'] ?? '');
if (!$id || $id < 1 || !api_valid_status($status)) {
    api_response(422, ['success' => false, 'message' => 'A valid task id and status are required.']);
}

$stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$status, $id, $userId]);
if ($stmt->rowCount() === 0) {
    $check = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
    $check->execute([$id, $userId]);
    if (!$check->fetch()) api_response(404, ['success' => false, 'message' => 'Task not found or you do not have permission to update it.']);
}
api_response(200, ['success' => true, 'message' => 'Task status updated successfully.']);
