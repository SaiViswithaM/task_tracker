<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function api_response(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_method(string $method): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        api_response(405, ['success' => false, 'message' => 'Method not allowed.']);
    }
}

function api_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        api_response(400, ['success' => false, 'message' => 'Request body must be valid JSON.']);
    }
    return $data;
}

function api_require_login(): int {
    if (empty($_SESSION['user_id'])) {
        api_response(401, ['success' => false, 'message' => 'Please log in first.']);
    }
    return (int)$_SESSION['user_id'];
}

function api_verify_csrf(): void {
    $submitted = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';
    if (!is_string($submitted) || !is_string($stored) || $submitted === '' || !hash_equals($stored, $submitted)) {
        api_response(403, ['success' => false, 'message' => 'Missing or invalid CSRF token. Call GET /api/csrf.php and send its csrf_token as X-CSRF-Token.']);
    }
}

function api_valid_status(string $status): bool {
    return in_array($status, ['Pending', 'In Progress', 'Completed'], true);
}
