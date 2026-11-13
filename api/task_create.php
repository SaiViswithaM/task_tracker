<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
$userId = api_require_login();
api_verify_csrf();
$data = api_input();

$title = trim((string)($data['title'] ?? ''));
$description = trim((string)($data['description'] ?? ''));
$status = (string)($data['status'] ?? 'Pending');

$errors = [];
if ($title === '') $errors[] = 'Task title is required.';
if (mb_strlen($title) > 255) $errors[] = 'Task title must be 255 characters or fewer.';
if (!api_valid_status($status)) $errors[] = 'Status must be Pending, In Progress or Completed.';
if ($errors) api_response(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);

$stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)');
$stmt->execute([$userId, $title, $description, $status]);
$id = (int)$pdo->lastInsertId();

$get = $pdo->prepare('SELECT id, title, description, status, created_at FROM tasks WHERE id = ? AND user_id = ?');
$get->execute([$id, $userId]);
api_response(201, ['success' => true, 'message' => 'Task created successfully.', 'task' => $get->fetch()]);
