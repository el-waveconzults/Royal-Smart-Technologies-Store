<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
$pdo = db();
$sessionId = session_id();

function getProductById(PDO $pdo, int $id): ?array {
  $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

// --- CART HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {
  $action = $_POST['cart_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'shop-list-view.php';
  
  if ($productId > 0) {
    if (!isset($_SESSION['cart'])) {
      $_SESSION['cart'] = [];
    }
    
    if ($action === 'add') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart_message'] = 'Item already exists in the cart';
      } else {
        $prod = getProductById($pdo, $productId);
        if ($prod) {
          $_SESSION['cart'][$productId] = [
            'id' => (int)$prod['id'],
            'name' => $prod['name'],
            'price' => (float)$prod['price'],
            'image' => $prod['image'],
            'quantity' => 1,
          ];
          $_SESSION['cart_message'] = '';
          
          // DB Sync
          $stmt = $pdo->prepare("INSERT IGNORE INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, 1)");
          $stmt->execute([$sessionId, $productId]);
        }
      }
      $redirect = 'shop-list-view.php';
    } elseif ($action === 'increment') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
        
        // DB Sync
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
      }
      $redirect = 'shop-list-view.php?cart_open=1';
    } elseif ($action === 'decrement') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']--;
        
        // DB Sync
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
        
        if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
          unset($_SESSION['cart'][$productId]);
          
          // DB Sync Remove
          $stmt = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?");
          $stmt->execute([$sessionId, $productId]);
        }
      }
      $redirect = 'shop-list-view.php?cart_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}

// --- WISHLIST HANDLER ---
$wishMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wish_action'])) {
  $action = $_POST['wish_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'shop-list-view.php';
  
  if ($productId > 0) {
    if (!isset($_SESSION['wishlist'])) {
      $_SESSION['wishlist'] = [];
    }
    
    if ($action === 'add') {
      if (isset($_SESSION['wishlist'][$productId])) {
        $_SESSION['wish_message'] = 'Item already exists in the wishlist';
      } else {
        $name = isset($_POST['product_name']) ? trim((string)$_POST['product_name']) : '';
        $price = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0.0;
        $image = isset($_POST['product_image']) ? trim((string)$_POST['product_image']) : '';
        $dbId = isset($_POST['product_db_id']) ? (int)$_POST['product_db_id'] : 0;
        
        if ($name !== '' && $price > 0 && $image !== '') {
          $_SESSION['wishlist'][$productId] = [
            'id' => $productId,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'db_id' => $dbId,
          ];
          $_SESSION['wish_message'] = '';
          
          // DB Sync
          $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (session_id, product_id) VALUES (?, ?)");
          $stmt->execute([$sessionId, $productId]);
        } else {
          $_SESSION['wish_message'] = 'Invalid product data';
        }
      }
      $redirect = 'shop-list-view.php';
    } elseif ($action === 'remove') {
      if (isset($_SESSION['wishlist'][$productId])) {
        unset($_SESSION['wishlist'][$productId]);
        
        // DB Sync
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
      }
      $redirect = 'shop-list-view.php?wish_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}

// --- COMPARE HANDLER ---
$compareMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compare_action'])) {
  $action = $_POST['compare_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'shop-list-view.php';
  
  if ($productId > 0) {
    if (!isset($_SESSION['compare'])) {
      $_SESSION['compare'] = [];
    }
    
    if ($action === 'add') {
      if (isset($_SESSION['compare'][$productId])) {
        $_SESSION['compare_message'] = 'Item already exists in compare';
      } else {
        $name = isset($_POST['product_name']) ? trim((string)$_POST['product_name']) : '';
        $price = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0.0;
        $image = isset($_POST['product_image']) ? trim((string)$_POST['product_image']) : '';
        $dbId = isset($_POST['product_db_id']) ? (int)$_POST['product_db_id'] : 0;
        
        if ($name !== '' && $price > 0 && $image !== '') {
          $_SESSION['compare'][$productId] = [
            'id' => $productId,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'db_id' => $dbId,
          ];
          $_SESSION['compare_message'] = '';
          
          // DB Sync
          $stmt = $pdo->prepare("INSERT IGNORE INTO compare (session_id, product_id) VALUES (?, ?)");
          $stmt->execute([$sessionId, $productId]);
        } else {
          $_SESSION['compare_message'] = 'Invalid product data';
        }
      }
      $redirect = 'shop-list-view.php';
    } elseif ($action === 'remove') {
      if (isset($_SESSION['compare'][$productId])) {
        unset($_SESSION['compare'][$productId]);
        
        // DB Sync
        $stmt = $pdo->prepare("DELETE FROM compare WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
      }
      $redirect = 'shop-list-view.php?compare_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}

// --- VIEW DATA PREP ---
$cart = $_SESSION['cart'] ?? [];
$cartCount = 0;
$cartTotal = 0.0;
foreach ($cart as $item) {
  $cartCount += (int)$item['quantity'];
  $cartTotal += (float)$item['price'] * (int)$item['quantity'];
}
$cartMessage = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
$cartOpen = isset($_GET['cart_open']) && $_GET['cart_open'] === '1';

$wish = $_SESSION['wishlist'] ?? [];
$wishCount = count($wish);
$wishMessage = $_SESSION['wish_message'] ?? '';
unset($_SESSION['wish_message']);
$wishOpen = isset($_GET['wish_open']) && $_GET['wish_open'] === '1';

$compare = $_SESSION['compare'] ?? [];
$compareCount = count($compare);
$compareMessage = $_SESSION['compare_message'] ?? '';
unset($_SESSION['compare_message']);
$compareOpen = isset($_GET['compare_open']) && $_GET['compare_open'] === '1';

// Fetch products for "Shop list view" category
$stmtCat = $pdo->prepare("SELECT id FROM categories WHERE slug = 'shop_list_view'");
$stmtCat->execute();
$catId = $stmtCat->fetchColumn();

$products = [];
if ($catId) {
    $stmtProd = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY id DESC");
    $stmtProd->execute([$catId]);
    $products = $stmtProd->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Shop List View - Royal Smart Technologies</title>
    <link rel="stylesheet" href="styles.css?v=20260120" />
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
  </head>
  <body class="shop-list-page">
    <header class="site-header">
      <div class="topbar">
        <div class="logo">
          <img src="../images/logos/Royal smart logo.jpg" alt="Royal logo" />
          <span>Royal smart technologies</span>
        </div>
        <div class="search-area">
          <input id="search" placeholder="Search Product....." />
          <select id="category-select">
            <option>Laptops</option>
            <option>Phones</option>
            <option>Accessories</option>
          </select>
          <button id="search-btn">Search</button>
        </div>
        <div class="icons">
          <a href="shop-list-view.php?wish_open=1" class="icon-btn wish-button">
            ♡
            <span class="cart-badge"<?= $wishCount > 0 ? '' : ' hidden' ?>><?= $wishCount ?></span>
          </a>
          <a href="shop-list-view.php?cart_open=1" class="icon-btn cart-button">
            🛒
            <span class="cart-badge"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= $cartCount ?></span>
          </a>
          <a href="shop-list-view.php?compare_open=1" class="icon-btn compare-button">
            ⇄
            <span class="cart-badge"<?= $compareCount > 0 ? '' : ' hidden' ?>><?= $compareCount ?></span>
          </a>
        </div>
      </div>
      <nav class="main-nav">
        <div class="categories-wrapper">
          <button class="categories-toggle" id="categoriesToggle">☰ All Categories ▾</button>
          <aside class="categories-panel" id="categoriesPanel" aria-hidden="true">
            <ul>
              <li>Phones ▸
                <ul>
                  <li><a href="shop-list-view.php">Iphone</a></li>
                  <li><a href="shop-list-view.php">Samsung</a></li>
                </ul>
              </li>
              <li>Laptops ▸
                <ul>
                  <li><a href="shop-list-view.php">Macbook</a></li>
                  <li><a href="shop-list-view.php">HP laptops</a></li>
                </ul>
              </li>
              <li><a href="shop-list-view.php">Software Accessories</a></li>
              <li><a href="shop-list-view.php">Tablet / iPad</a></li>
              <li><a href="shop-list-view.php">Chargers</a></li>
            </ul>
          </aside>
        </div>
        <ul class="nav-links">
          <li><a href="index.php">Home</a></li>
          <li class="dropdown-container">
            <a href="javascript:void(0)" class="dropdown-trigger">Shop ▾</a>
            <ul class="dropdown">
              <li><a href="shop-list-view.php" aria-current="page">Shop List View</a></li>
              <li><a href="shop-product-details.php">Shop Product-details</a></li>
            </ul>
          </li>
          <li class="dropdown-container">
            <a href="javascript:void(0)" class="dropdown-trigger">Pages ▾</a>
            <ul class="dropdown">
              <li><a href="blog.php">Blog</a></li>
              <li><a href="faq.php">FAQ</a></li>
            </ul>
          </li>
          <li><a href="about.php">About</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </nav>
    </header>
    <div class="cart-panel-overlay" id="cartOverlay"<?= $cartOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="cartPanel">
        <div class="cart-header">
          <h2>Your Cart</h2>
          <a class="cart-close" href="shop-list-view.php">×</a>
        </div>
        <div class="cart-message" id="cartMessage"><?= htmlspecialchars($cartMessage) ?></div>
        <div class="cart-items" id="cartItems">
          <?php if (!$cart): ?>
            <p>Your cart is empty.</p>
          <?php else: ?>
            <?php foreach ($cart as $item): ?>
              <div class="cart-item">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
                <div class="cart-item-info">
                  <div class="cart-item-title"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="cart-item-price">₦<?= number_format((float)$item['price'] * (int)$item['quantity']) ?></div>
                </div>
                <div class="cart-quantity">
                  <form method="post">
                    <input type="hidden" name="cart_action" value="decrement" />
                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>" />
                    <button type="submit">-</button>
                  </form>
                  <span><?= (int)$item['quantity'] ?></span>
                  <form method="post">
                    <input type="hidden" name="cart_action" value="increment" />
                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>" />
                    <button type="submit">+</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="cart-footer">
          <div class="cart-total">
            <span>Total:</span>
            <span>₦<span id="cartTotal"><?= number_format($cartTotal) ?></span></span>
          </div>
          <button class="cart-buy" id="cartBuy" type="button"<?= $cartCount > 0 ? '' : ' disabled' ?>>Buy Now</button>
        </div>
      </aside>
    </div>
    <div class="cart-panel-overlay" id="compareOverlay"<?= $compareOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="comparePanel">
        <div class="cart-header">
          <h2>Compare</h2>
          <a class="cart-close" href="shop-list-view.php">×</a>
        </div>
        <div class="cart-message" id="compareMessage"><?= htmlspecialchars($compareMessage) ?></div>
        <div class="cart-items" id="compareItems">
          <?php if (!$compare): ?>
            <p>No items to compare.</p>
          <?php else: ?>
            <?php foreach ($compare as $item): ?>
              <div class="cart-item">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
                <div class="cart-item-info">
                  <div class="cart-item-title"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="cart-item-price">₦<?= number_format((float)$item['price']) ?></div>
                </div>
                <div class="cart-quantity">
                  <a href="shop-product-details.php?id=<?= (int)($item['db_id'] ?? $item['id']) ?>">⤢</a>
                  <form method="post">
                    <input type="hidden" name="compare_action" value="remove" />
                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>" />
                    <button type="submit">×</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </aside>
    </div>
    <div class="cart-panel-overlay" id="wishOverlay"<?= $wishOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="wishPanel">
        <div class="cart-header">
          <h2>Your Wishlist</h2>
          <a class="cart-close" href="shop-list-view.php">×</a>
        </div>
        <div class="cart-message" id="wishMessage"><?= htmlspecialchars($wishMessage) ?></div>
        <div class="cart-items" id="wishItems">
          <?php if (!$wish): ?>
            <p>Your wishlist is empty.</p>
          <?php else: ?>
            <?php foreach ($wish as $item): ?>
              <div class="cart-item">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
                <div class="cart-item-info">
                  <div class="cart-item-title"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="cart-item-price">₦<?= number_format((float)$item['price']) ?></div>
                </div>
                <div class="cart-quantity">
                  <form method="post">
                    <input type="hidden" name="wish_action" value="remove" />
                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>" />
                    <button type="submit">×</button>
                  </form>
                  <form method="post">
                    <input type="hidden" name="cart_action" value="add" />
                    <input type="hidden" name="product_id" value="<?= (int)($item['db_id'] ?? $item['id']) ?>" />
                    <button type="submit">🛒</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </aside>
    </div>
    <main class="shop-list-main">
      <section class="shop-list-hero">
        <div class="container">
          <div class="hero-content">
            <h1>Shop List View</h1>
            <p>Discover colorful gadgets, accessories, and more — curated for you.</p>
          </div>
        </div>
      </section>
      <section class="shop-list" id="shopList">
        <div class="container">
          <div class="products-grid">
            <?php if (empty($products)): ?>
              <p>No products found in "Shop list view" category. Please add products from Admin Dashboard.</p>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <?php
                  $pId = (int)$p['id'];
                  $pName = htmlspecialchars($p['name']);
                  $pPrice = (float)$p['price'];
                  $pOrig = $p['original_price'] ? (float)$p['original_price'] : null;
                  $pImage = htmlspecialchars($p['image']);
                ?>
                <div class="product-card">
                  <div class="product-img">
                    <img src="<?= $pImage ?>" alt="<?= $pName ?>" />
                    <div class="product-actions">
                      <a href="shop-product-details.php?id=<?= $pId ?>" title="View Details">⤢</a>
                      <form method="post">
                        <input type="hidden" name="wish_action" value="add" />
                        <input type="hidden" name="product_id" value="<?= $pId ?>" />
                        <input type="hidden" name="product_db_id" value="<?= $pId ?>" />
                        <input type="hidden" name="product_name" value="<?= $pName ?>" />
                        <input type="hidden" name="product_price" value="<?= $pPrice ?>" />
                        <input type="hidden" name="product_image" value="<?= $pImage ?>" />
                        <button title="Wishlist">♡</button>
                      </form>
                      <form method="post">
                        <input type="hidden" name="compare_action" value="add" />
                        <input type="hidden" name="product_id" value="<?= $pId ?>" />
                        <input type="hidden" name="product_db_id" value="<?= $pId ?>" />
                        <input type="hidden" name="product_name" value="<?= $pName ?>" />
                        <input type="hidden" name="product_price" value="<?= $pPrice ?>" />
                        <input type="hidden" name="product_image" value="<?= $pImage ?>" />
                        <button title="Compare">⇄</button>
                      </form>
                    </div>
                  </div>
                  <div class="product-info">
                    <div class="rating">★★★★★</div>
                    <h3><?= $pName ?></h3>
                    <div class="price">
                      <?php if ($pOrig): ?>
                        <span class="old-price">₦<?= number_format($pOrig) ?></span>
                      <?php endif; ?>
                      <span class="new-price">₦<?= number_format($pPrice) ?></span>
                    </div>
                    <form method="post">
                      <input type="hidden" name="cart_action" value="add" />
                      <input type="hidden" name="product_id" value="<?= $pId ?>" />
                      <button type="submit" class="add-to-cart">Add To Cart</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
    <footer class="site-footer">
      <div class="container">
        <div class="footer-benefits">
          <div class="benefit"><div class="icon">🚚</div><div class="text"><div class="title">Free Shipping</div><div class="desc">When ordering over $100</div></div></div>
          <div class="benefit"><div class="icon">↩️</div><div class="text"><div class="title">Free Return</div><div class="desc">Get Return within 30 days</div></div></div>
          <div class="benefit"><div class="icon">🔒</div><div class="text"><div class="title">Secure Payment</div><div class="desc">100% Secure Online Payment</div></div></div>
          <div class="benefit"><div class="icon">🏆</div><div class="text"><div class="title">Best Quality</div><div class="desc">Original Product Guaranteed</div></div></div>
        </div>
        <div class="footer-main">
          <div class="footer-brand">
            <div class="brand-row"><img src="../images/logos/Royal smart logo.jpg" alt="Royal logo" /><span>Royal smart technologies</span></div>
            <ul class="footer-links"><li><a href="#">Track Order</a></li><li><a href="#">Delivery & Returns</a></li><li><a href="#">Warranty</a></li></ul>
          </div>
          <div class="footer-col"><h4>About Us</h4><ul class="footer-links"><li><a href="#">Rave's Story</a></li><li><a href="#">Work With Us</a></li><li><a href="#">Corporate News</a></li><li><a href="#">Investors</a></li></ul></div>
          <div class="footer-col"><h4>Useful Links</h4><ul class="footer-links"><li><a href="#">Secure Payment</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Use</a></li><li><a href="#">Archived Products</a></li></ul></div>
          <div class="footer-col"><h4>Contact Info</h4><ul class="footer-links"><li><span>Address:</span> 4517 Washington Ave. Manchester, Kentucky 39495</li><li><span>Phone:</span> +880171889547</li><li><span>Email:</span> Demo@gmail.com</li></ul></div>
        </div>
        <div class="footer-bottom">
          <p>© 2024 Royal Smart Technologies. All Rights Reserved</p>
          <div class="payment-icons"><img src="../images/logos/payment-img-1.png" alt="Payment" /></div>
        </div>
      </div>
    </footer>
    <script src="script.js"></script>
  </body>
</html>
