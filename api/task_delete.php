<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
$userId = api_require_login();
api_verify_csrf();
$data = api_input();

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) api_response(422, ['success' => false, 'message' => 'A valid task id is required.']);

$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
if ($stmt->rowCount() === 0) {
    api_response(404, ['success' => false, 'message' => 'Task not found or you do not have permission to delete it.']);
}
api_response(200, ['success' => true, 'message' => 'Task deleted successfully.']);
