<?php
declare(strict_types=1);
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $ok = hash_equals('Admin', $u) && hash_equals(password_hash('12345', PASSWORD_DEFAULT), password_hash($p, PASSWORD_DEFAULT));
    if ($u === 'Admin' && $p === '12345') {
        $_SESSION['admin'] = true;
        header('Location: /Royal%20Smart%20Technologies%20Store/backend/AdminDashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <link rel="stylesheet" href="Dashboard.css" />
  </head>
  <body>
    <div style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f5f5f5">
      <form method="post" style="background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,.1);width:320px">
        <h2 style="margin:0 0 16px">Admin Login</h2>
        <?php if ($error): ?>
          <div style="color:#ff3366;margin-bottom:12px"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <label>Username</label>
        <input name="username" required style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
        <label>Password</label>
        <input type="password" name="password" required style="width:100%;padding:10px;margin:6px 0 16px;border:1px solid #ddd;border-radius:6px" />
        <button type="submit" style="width:100%;padding:10px;background:#111827;color:#fff;border:none;border-radius:6px">Login</button>
      </form>
    </div>
  </body>
</html>
