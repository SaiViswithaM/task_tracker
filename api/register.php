<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
api_verify_csrf();
$data = api_input();

$name = trim((string)($data['name'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

$errors = [];
if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Name is required and must be 100 characters or fewer.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) $errors[] = 'A valid email address is required.';
if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
if ($errors) api_response(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) api_response(409, ['success' => false, 'message' => 'An account with this email already exists.']);

$insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
$userId = (int)$pdo->lastInsertId();

session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

api_response(201, [
    'success' => true,
    'message' => 'Account created successfully.',
    'user' => ['id' => $userId, 'name' => $name, 'email' => $email],
    'csrf_token' => $_SESSION['csrf_token']
]);
