<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/db.php';
$pdo = db();

// Fetch all cart items with product details
// Joining cart_items with products table
$stmt = $pdo->query("
    SELECT 
        c.session_id,
        c.quantity,
        c.created_at,
        p.name,
        p.price,
        p.image
    FROM cart_items c
    JOIN products p ON c.product_id = p.id
    ORDER BY c.created_at DESC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate badge count (distinct sessions or total items) - For the sidebar
// We can do this in the dashboard, but for this page we just show the list.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Orders (Active Carts)</title>
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
    <link rel="stylesheet" href="Dashboard.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style>
        .badge-red {
            background-color: #ff4444;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px;
        }
    </style>
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
                <a href="admin_wishlist.php"> <li>Wishlist</li> </a>
                <a href="admin_compare.php"> <li>Compare</li> </a>
                <a href="admin_orders.php"> <li class="active">Orders <span class="badge-red"><?= count($rows) ?></span></li> </a>
            </ul>
        </nav>

        <main class="content">
            <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h1>Active Orders (Carts)</h1>
            </header>

            <div style="background:white;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                <table style="width:100%;border-collapse:collapse;margin-top:20px;">
                    <thead>
                        <tr style="background:#f9f9f9;text-align:left;">
                            <th style="padding:12px;border-bottom:2px solid #eee;">Date</th>
                            <th style="padding:12px;border-bottom:2px solid #eee;">Session ID</th>
                            <th style="padding:12px;border-bottom:2px solid #eee;">Product</th>
                            <th style="padding:12px;border-bottom:2px solid #eee;">Price</th>
                            <th style="padding:12px;border-bottom:2px solid #eee;">Qty</th>
                            <th style="padding:12px;border-bottom:2px solid #eee;">Total</th>
                            <th style="padding:12px;border-bottom:2px solid #eee;">Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach($rows as $r): ?>
                            <tr>
                                <td style="padding:12px;border-bottom:1px solid #eee;"><?= htmlspecialchars($r['created_at']) ?></td>
                                <td style="padding:12px;border-bottom:1px solid #eee;">
                                    <span style="font-family:monospace;background:#eee;padding:2px 6px;border-radius:4px;font-size:12px;" title="<?= htmlspecialchars($r['session_id']) ?>">
                                        <?= htmlspecialchars(substr($r['session_id'], 0, 8)) ?>...
                                    </span>
                                </td>
                                <td style="padding:12px;border-bottom:1px solid #eee;"><?= htmlspecialchars($r['name']) ?></td>
                                <td style="padding:12px;border-bottom:1px solid #eee;">₦<?= number_format((float)$r['price'], 2) ?></td>
                                <td style="padding:12px;border-bottom:1px solid #eee;"><?= (int)$r['quantity'] ?></td>
                                <td style="padding:12px;border-bottom:1px solid #eee;">₦<?= number_format((float)$r['price'] * (int)$r['quantity'], 2) ?></td>
                                <td style="padding:12px;border-bottom:1px solid #eee;">
                                    <img src="<?= htmlspecialchars($r['image']) ?>" style="height:40px;width:40px;object-fit:cover;border-radius:4px;">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="padding:20px;text-align:center;color:#888;">No active orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
