<?php
declare(strict_types=1);

$host = "db01.dbhost.dev";
$port = "5051";
$dbname = "db_45533hmzd";
$username = "user_45533hmzd";
$password = "p45533hmzd";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit("Unable to connect to the database. Check config/database.php.");
}