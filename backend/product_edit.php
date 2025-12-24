<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/db.php';
$pdo = db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header("Location: products.php");
    exit;
}
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $original = strlen($_POST['original_price'] ?? '') ? (float)$_POST['original_price'] : null;
    $image = trim($_POST['image'] ?? '');
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    if ($name && $price > 0 && $image) {
        $u = $pdo->prepare("UPDATE products SET name=?, price=?, original_price=?, image=?, category_id=? WHERE id=?");
        $u->execute([$name, $price, $original, $image, $category_id ?: null, $id]);
        header("Location: products.php");
        exit;
    } else {
        $msg = 'Please fill required fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Product</title>
  <link rel="stylesheet" href="Dashboard.css" />
</head>
<body>
  <div class="content" style="max-width:800px;margin:20px auto">
    <h2>Edit Product</h2>
    <?php if ($msg): ?><div style="color:#ff3366"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post">
      <label>Name</label>
      <input name="name" value="<?= htmlspecialchars($row['name']) ?>" required style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Price</label>
      <input type="number" name="price" step="0.01" value="<?= htmlspecialchars((string)$row['price']) ?>" required style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Original Price</label>
      <input type="number" name="original_price" step="0.01" value="<?= htmlspecialchars($row['original_price'] !== null ? (string)$row['original_price'] : '') ?>" style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Image path</label>
      <input name="image" value="<?= htmlspecialchars($row['image']) ?>" required style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Category</label>
      <select name="category_id" style="width:100%;padding:10px;margin:6px 0 16px;border:1px solid #ddd;border-radius:6px">
        <option value="">None</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ($row['category_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" style="padding:10px 16px;background:#111827;color:#fff;border:none;border-radius:6px">Save</button>
    </form>
  </div>
</body>
</html>
