<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
$userId = api_require_login();
api_verify_csrf();
$data = api_input();

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
$title = trim((string)($data['title'] ?? ''));
$description = trim((string)($data['description'] ?? ''));
$status = (string)($data['status'] ?? 'Pending');

$errors = [];
if (!$id || $id < 1) $errors[] = 'A valid task id is required.';
if ($title === '') $errors[] = 'Task title is required.';
if (mb_strlen($title) > 255) $errors[] = 'Task title must be 255 characters or fewer.';
if (!api_valid_status($status)) $errors[] = 'Status must be Pending, In Progress or Completed.';
if ($errors) api_response(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);

$check = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
$check->execute([$id, $userId]);
if (!$check->fetch()) api_response(404, ['success' => false, 'message' => 'Task not found or you do not have permission to update it.']);

$update = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?');
$update->execute([$title, $description, $status, $id, $userId]);
$get = $pdo->prepare('SELECT id, title, description, status, created_at FROM tasks WHERE id = ? AND user_id = ?');
$get->execute([$id, $userId]);
api_response(200, ['success' => true, 'message' => 'Task updated successfully.', 'task' => $get->fetch()]);
