<?php
declare(strict_types=1);

$host = 'localhost';
$port = 3305; // Change to 3306 if your XAMPP MySQL port is 3306.
$dbname = 'task_tracker';
$username = 'root';
$password = ''; // Change this if your MySQL root account has a password.
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Do not expose database credentials or raw connection errors to visitors.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to connect to the database. Check config/database.php and ensure MySQL is running.');
}
