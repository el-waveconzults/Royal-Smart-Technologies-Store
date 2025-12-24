<?php
declare(strict_types=1);
session_start();
function require_admin(): void {
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header('Location: /Royal%20Smart%20Technologies%20Store/backend/login.php');
        exit;
    }
}
