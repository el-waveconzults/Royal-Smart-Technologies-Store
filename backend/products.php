<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/db.php';
$pdo = db();
$rows = $pdo->query("
  SELECT p.id, p.name, p.price, p.original_price, p.image, c.name AS category_name
  FROM products p
  LEFT JOIN categories c ON c.id = p.category_id
  ORDER BY p.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products</title>
  <link rel="stylesheet" href="Dashboard.css" />
</head>
<body>
  <div class="content" style="max-width:1100px;margin:20px auto">
    <h2>Products</h2>
    <a href="product_create.php" style="display:inline-block;margin:10px 0;padding:8px 12px;background:#111827;color:#fff;border-radius:6px">Create New</a>
    <table style="width:100%;border-collapse:collapse;background:#fff">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">ID</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">Name</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">Category</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">Price</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">Original</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">Image</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #eee">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3"><?= (int)$r['id'] ?></td>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3"><?= htmlspecialchars($r['name']) ?></td>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3"><?= htmlspecialchars($r['category_name'] ?? '') ?></td>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3">₦<?= number_format((float)$r['price'], 2) ?></td>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3"><?= $r['original_price'] !== null ? '₦'.number_format((float)$r['original_price'], 2) : '' ?></td>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3"><span style="font-size:12px"><?= htmlspecialchars($r['image']) ?></span></td>
          <td style="padding:10px;border-bottom:1px solid #f3f3f3">
            <a href="product_edit.php?id=<?= (int)$r['id'] ?>" style="margin-right:6px">Edit</a>
            <form method="post" action="product_delete.php" style="display:inline">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
              <button type="submit" style="background:#ff3366;color:#fff;border:none;border-radius:4px;padding:6px 10px">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
