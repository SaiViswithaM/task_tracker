<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
api_verify_csrf();
$data = api_input();

$email = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    api_response(422, ['success' => false, 'message' => 'Enter a valid email address and password.']);
}

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    api_response(401, ['success' => false, 'message' => 'Incorrect email or password.']);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

api_response(200, [
    'success' => true,
    'message' => 'Login successful.',
    'user' => ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email']],
    'csrf_token' => $_SESSION['csrf_token']
]);
