<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        $pdo = db();
        $d = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $d->execute([$id]);
    }
}
header("Location: products.php");
exit;
