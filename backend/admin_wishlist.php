<?php
require_once __DIR__.'/auth.php';
require_once __DIR__.'/db.php';
require_admin();
$pdo = db();

$rows = $pdo->query("
    SELECT w.id, w.session_id, w.created_at, p.name, p.price, p.image 
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    ORDER BY w.created_at DESC
")->fetchAll();

// Count active cart items for the badge
$cartCount = $pdo->query("SELECT COUNT(*) FROM cart_items")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Wishlist</title>
  <link rel="stylesheet" href="Dashboard.css" />
</head>
<body>
  <div class="dashboard">
    <nav class="sidebar">
        <div class="logo">
          <img src="../images/logos/Royal smart logo.jpg" alt="Royal" style="height: 45px; width: 45px; border-radius: 30px;"/>
        </div>
        <ul class="nav-items">
         <a href="AdminDashboard.php"> <li>Dashboard</li></a>
         <a href="product_create.php"> <li>Create</li> </a>
          <a href="products.php">  <li>Tables</li> </a>
          <a href="admin_wishlist.php"> <li class="active">Wishlist</li> </a>
          <a href="admin_compare.php"> <li>Compare</li> </a>
          <a href="admin_orders.php"> <li>Orders <span class="badge" style="background:var(--red);border-radius:3px;"><?= $cartCount ?></span></li> </a>
        </ul>
      </nav>
      <main class="content">
        <h2>Active Wishlists</h2>
        <div class="chart-container" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;margin-top:20px;">
                <thead>
                    <tr style="background:#f9f9f9;text-align:left;">
                        <th style="padding:12px;border-bottom:2px solid #eee;">Date</th>
                        <th style="padding:12px;border-bottom:2px solid #eee;">Session ID</th>
                        <th style="padding:12px;border-bottom:2px solid #eee;">Product</th>
                        <th style="padding:12px;border-bottom:2px solid #eee;">Price</th>
                        <th style="padding:12px;border-bottom:2px solid #eee;">Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="5" style="padding:20px;text-align:center;">No wishlist items found.</td></tr>
                    <?php else: ?>
                        <?php foreach($rows as $r): ?>
                        <tr>
                            <td style="padding:12px;border-bottom:1px solid #eee;"><?= htmlspecialchars($r['created_at']) ?></td>
                            <td style="padding:12px;border-bottom:1px solid #eee;"><span style="font-family:monospace;background:#eee;padding:2px 6px;border-radius:4px;font-size:12px;"><?= htmlspecialchars(substr($r['session_id'], 0, 8)) ?>...</span></td>
                            <td style="padding:12px;border-bottom:1px solid #eee;"><?= htmlspecialchars($r['name']) ?></td>
                            <td style="padding:12px;border-bottom:1px solid #eee;">₦<?= number_format((float)$r['price'], 2) ?></td>
                            <td style="padding:12px;border-bottom:1px solid #eee;"><img src="<?= htmlspecialchars($r['image']) ?>" style="height:40px;width:40px;object-fit:cover;border-radius:4px;"></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      </main>
  </div>
</body>
</html>
