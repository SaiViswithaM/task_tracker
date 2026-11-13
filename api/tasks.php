<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('GET');
$userId = api_require_login();

$search = trim((string)($_GET['search'] ?? ''));
$status = (string)($_GET['status'] ?? '');
if ($status !== '' && !api_valid_status($status)) {
    api_response(422, ['success' => false, 'message' => 'Invalid status filter.']);
}

$sql = 'SELECT id, title, description, status, created_at FROM tasks WHERE user_id = :user_id';
$params = [':user_id' => $userId];
if ($search !== '') {
    $sql .= ' AND title LIKE :search';
    $params[':search'] = '%' . $search . '%';
}
if ($status !== '') {
    $sql .= ' AND status = :status';
    $params[':status'] = $status;
}
$sql .= ' ORDER BY created_at DESC, id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

api_response(200, [
    'success' => true,
    'count' => count($tasks),
    'tasks' => $tasks
]);
