<?php
require_once __DIR__ . '/_bootstrap.php';
api_method('GET');
api_response(200, [
    'success' => true,
    'csrf_token' => csrf_token(),
    'message' => 'Save this token and send it in the X-CSRF-Token header for POST requests.'
]);
