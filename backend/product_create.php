<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/db.php';
$pdo = db();
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $original = strlen($_POST['original_price'] ?? '') ? (float)$_POST['original_price'] : null;
    $image = trim($_POST['image'] ?? '');
    if (isset($_FILES['image_file']) && is_array($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image_file']['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
        ];
        if (!isset($allowed[$mime])) {
            $msg = 'Invalid image type.';
        } else {
            $namePart = pathinfo($_FILES['image_file']['name'], PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', $namePart);
            $ext = $allowed[$mime];
            $finalName = $safeBase.'-'.time().'.'.$ext;
            $targetDir = __DIR__ . '/../uploads/products';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $finalName;
            if (move_uploaded_file($tmp, $targetPath)) {
                $image = '../uploads/products/'.$finalName;
            } else {
                $msg = 'Failed to save uploaded image.';
            }
        }
    }
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    if ($name && $price > 0 && $image) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, original_price, image, category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $original, $image, $category_id ?: null]);
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
  <title>Create Product</title>
  <link rel="stylesheet" href="Dashboard.css" />
</head>
<body>
  <div class="content" style="max-width:800px;margin:20px auto">
    <h2>Create Product</h2>
    <?php if ($msg): ?><div style="color:#ff3366"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <label>Name</label>
      <input name="name" required style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Price</label>
      <input type="number" name="price" step="0.01" required style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Original Price</label>
      <input type="number" name="original_price" step="0.01" style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Upload Image</label>
      <input type="file" name="image_file" accept="image/*" style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Or Image path</label>
      <input name="image" placeholder="../images/homepage-one/product-img/..." style="width:100%;padding:10px;margin:6px 0 12px;border:1px solid #ddd;border-radius:6px" />
      <label>Category</label>
      <select name="category_id" style="width:100%;padding:10px;margin:6px 0 16px;border:1px solid #ddd;border-radius:6px">
        <option value="">None</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" style="padding:10px 16px;background:#111827;color:#fff;border:none;border-radius:6px">Create</button>
    </form>
  </div>
</body>
</html>
