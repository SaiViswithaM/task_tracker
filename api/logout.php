<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('POST');
$userId = api_require_login();
api_verify_csrf();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
api_response(200, ['success' => true, 'message' => 'Logged out successfully.']);
