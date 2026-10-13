
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function requireRole(string $role): void
{
    if (empty($_SESSION["user_id"])) {
        header("Location: ../login.php");
        exit;
    }

    if (($_SESSION["role"] ?? "") !== $role) {
        http_response_code(403);
        exit("Access denied. You do not have permission to view this page.");
    }
}