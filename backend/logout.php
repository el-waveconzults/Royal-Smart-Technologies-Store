<?php
declare(strict_types=1);
session_start();
$_SESSION = [];
session_destroy();
header('Location: /Royal%20Smart%20Technologies%20Store/backend/login.php');
exit;
