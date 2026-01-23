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
function getProducts(PDO $pdo, string $slug): array {
  $stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.original_price, p.image FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE c.slug = ? ORDER BY p.id DESC");
  $stmt->execute([$slug]);
  return $stmt->fetchAll();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {
  $action = $_POST['cart_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'index.php';
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
      $redirect = 'index.php';
    } elseif ($action === 'increment') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
        
        // DB Sync
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
      }
      $redirect = 'index.php?cart_open=1';
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
      $redirect = 'index.php?cart_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wish_action'])) {
  $action = $_POST['wish_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'index.php';
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
      $redirect = 'index.php';
    } elseif ($action === 'remove') {
      if (isset($_SESSION['wishlist'][$productId])) {
        unset($_SESSION['wishlist'][$productId]);
        
        // DB Sync
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
      }
      $redirect = 'index.php?wish_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
$compareMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compare_action'])) {
  $action = $_POST['compare_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'index.php';
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
      $redirect = 'index.php';
    } elseif ($action === 'remove') {
      if (isset($_SESSION['compare'][$productId])) {
        unset($_SESSION['compare'][$productId]);
        
        // DB Sync
        $stmt = $pdo->prepare("DELETE FROM compare WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
      }
      $redirect = 'index.php?compare_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
$data = [
  'new_arrivals' => getProducts($pdo, 'new_arrivals'),
  'flash_sale' => getProducts($pdo, 'flash_sale'),
  'top_selling' => getProducts($pdo, 'top_selling'),
  'best_selling_week' => getProducts($pdo, 'best_selling_week'),
];
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
function renderGrid(array $items): string {
  $html = '';
  foreach ($items as $p) {
    $old = $p['original_price'];
    $id = (int)$p['id'];
    $price = (float)$p['price'];
    $html .= '<div class="product-card" data-product-id="'.$id.'" data-price="'.$price.'"><div class="product-img"><img src="'.htmlspecialchars($p['image']).'" alt="'.htmlspecialchars($p['name']).'" /><div class="product-actions"><a href="shop-product-details.php?id='.$id.'" title="View Details">⤢</a><form method="post"><input type="hidden" name="wish_action" value="add" /><input type="hidden" name="product_id" value="'.$id.'" /><input type="hidden" name="product_db_id" value="'.$id.'" /><input type="hidden" name="product_name" value="'.htmlspecialchars($p['name']).'" /><input type="hidden" name="product_price" value="'.$price.'" /><input type="hidden" name="product_image" value="'.htmlspecialchars($p['image']).'" /><button title="Wishlist">♡</button></form><form method="post"><input type="hidden" name="compare_action" value="add" /><input type="hidden" name="product_id" value="'.$id.'" /><input type="hidden" name="product_db_id" value="'.$id.'" /><input type="hidden" name="product_name" value="'.htmlspecialchars($p['name']).'" /><input type="hidden" name="product_price" value="'.$price.'" /><input type="hidden" name="product_image" value="'.htmlspecialchars($p['image']).'" /><button title="Compare">⇄</button></form></div></div><div class="product-info"><div class="rating">★★★★★</div><h3>'.htmlspecialchars($p['name']).'</h3><div class="price">'.($old !== null ? '<span class="old-price">₦'.number_format((float)$old).'</span>' : '').'<span class="new-price">₦'.number_format($price).'</span></div><form method="post"><input type="hidden" name="cart_action" value="add" /><input type="hidden" name="product_id" value="'.$id.'" /><button type="submit" class="add-to-cart">Add To Cart</button></form></div></div>';
  }
  return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Royal Smart Technologies</title>
    <link rel="stylesheet" href="styles.css?v=20260120" />
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
  </head>
  <body>
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
          <a href="index.php?wish_open=1" class="icon-btn wish-button">
            ♡
            <span class="cart-badge"<?= $wishCount > 0 ? '' : ' hidden' ?>><?= $wishCount ?></span>
          </a>
          <a href="?cart_open=1" class="icon-btn cart-button">
            🛒
            <span class="cart-badge"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= $cartCount ?></span>
          </a>
          <a href="index.php?compare_open=1" class="icon-btn compare-button">
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
              <li><a href="shop-list-view.php">Shop List View</a></li>
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
    <div class="cart-panel-overlay" id="wishOverlay"<?= $wishOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="wishPanel">
        <div class="cart-header">
          <h2>Your Wishlist</h2>
          <a class="cart-close" href="index.php">×</a>
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
    <div class="cart-panel-overlay" id="compareOverlay"<?= $compareOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="comparePanel">
        <div class="cart-header">
          <h2>Compare</h2>
          <a class="cart-close" href="index.php">×</a>
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
    <div class="cart-panel-overlay" id="cartOverlay"<?= $cartOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="cartPanel">
        <div class="cart-header">
          <h2>Your Cart</h2>
          <a class="cart-close" href="index.php">×</a>
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
    <main>
      <section class="hero">
        <div class="carousel" id="carousel">
          <div class="slide active" style="background-image: url('../images/homepage-one/slide image card one.jpg');">
            <div class="caption dark-text">
              <h2>Welcome</h2>
              <h1>To Royal Smart Technologies</h1>
              <p>Your No.1 Plug for premium Gadgets & Smart Devices.</p>
              <a class="btn" href="shop-list-view.php">Go Shopping ▸</a>
            </div>
          </div>
          <div class="slide" style="background-image: url('../images/homepage-one/slide image card two.webp');">
            <div class="caption dark-text">
              <h1>Royal Smart Technologies</h1>
              <p>Smart Choices. Royal Quality. Order today — get it delivered to your doorstep.</p>
              <a class="btn" href="shop-list-view.php">Browse Collection ▸</a>
            </div>
          </div>
          <div class="slide" style="background-image: url('../images/homepage-one/slide image card three.jpg');">
            <div class="caption">
              <h2>Latest Devices</h2>
              <h1>Discover the newest phones at competitive prices.</h1>
              <p>-100% Authentic</p>
              <a class="btn" href="shop-list-view.php">Shop Devices ▸</a>
            </div>
          </div>
          <button class="carousel-control prev" data-direction="prev">‹</button>
          <button class="carousel-control next" data-direction="next">›</button>
          <div class="indicators" id="indicators"></div>
        </div>
      </section>
      <section class="our-categories" id="ourCategories">
        <div class="container">
          <div class="section-header">
            <h2>Our Categories</h2>
            <a href="shop-list-view.php" class="view-all">View All</a>
          </div>
          <div class="categories-grid">
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/product-img/Iphone 14 pro max.jpg" alt="Iphone 14pro max" /></div><p>Iphone 14pro max</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Apple-iPhone-Air-Sky-Blue.webp" alt="Iphone 14 Air" /></div><p>Iphone 14 Air</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/MacBook-Pro-20211.webp" alt="Macbook" /></div><p>Macbook</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Home theater.jpeg" alt="Home Theater" /></div><p>Home Theater</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Ps4.jpg" alt="Ps4" /></div><p>Ps4</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Samsung-Galaxy-S22-Ultra.webp" alt="Samsung S22 Ultra" /></div><p>Samsung S22 Ultra</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Hp laptop.jpeg" alt="HP Laptop" /></div><p>HP Laptop</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/ipad-pro.jpeg" alt="Ipad" /></div><p>Ipad</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Galaxy-Tab.jpg" alt="Galaxy Tab" /></div><p>Galaxy Tab</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Laptop bags.jpeg" alt="Laptop Bags" /></div><p>Laptop Bags</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/bluetooth-headphones.webp" alt="Head Phones" /></div><p>Head Phones</p></div>
            <div class="category-item"><div class="img-wrapper"><img src="../images/homepage-one/Fast charger.webp" alt="Fast Chargers" /></div><p>Fast Chargers</p></div>
          </div>
        </div>
      </section>
      <section class="new-arrivals" id="newArrivals">
        <div class="container">
          <div class="section-header">
            <h2>NEW ARRIVALS</h2>
            <a href="shop-list-view.php" class="view-all">View All</a>
          </div>
          <div class="products-grid">
            <?= renderGrid($data['new_arrivals']); ?>
          </div>
        </div>
      </section>
      <section class="discount-banners">
        <div class="container">
          <div class="banners-grid">
            <div class="banner-item" style="background-image: url('../images/homepage-one/discount-1.jpg');">
              <div class="banner-content">
                <span class="banner-tag">New Style</span>
                <h2>Get <span class="highlight">65% Offer</span><br />& Make New<br />Fusion.</h2>
                <a href="shop-list-view.php" class="shop-now-btn">Shop Now ➝</a>
              </div>
            </div>
            <div class="banner-item" style="background-image: url('../images/homepage-one/discount-2.jpg');">
              <div class="banner-content">
                <span class="banner-tag">New Style</span>
                <h2>Get <span class="highlight">65% Offer</span><br />& Make New<br />Fusion.</h2>
                <a href="shop-list-view.php" class="shop-now-btn">Shop Now ➝</a>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="flash-sale" id="flashSale">
        <div class="container">
          <div class="flash-header">
            <h2>Flash Sale</h2>
            <div class="flash-countdown">
              <div class="count-item count-days"><div class="number" data-unit="days">0</div><div class="label">Days</div></div>
              <div class="count-item count-hours"><div class="number" data-unit="hours">0</div><div class="label">Hours</div></div>
              <div class="count-item count-minutes"><div class="number" data-unit="minutes">0</div><div class="label">Minutes</div></div>
              <div class="count-item count-seconds"><div class="number" data-unit="seconds">0</div><div class="label">seconds</div></div>
            </div>
            <a href="shop-list-view.php" class="view-all">View All</a>
          </div>
          <div class="products-grid flash-grid">
            <?= renderGrid($data['flash_sale']); ?>
          </div>
        </div>
      </section>
      <section class="style-offers" id="styleOffers">
        <div class="container">
          <div class="style-grid">
            <div class="style-card-one"><span class="style-tag">NEW STYLE</span><h2>Get 65% Offer & Make New Fusion.</h2><a href="shop-list-view.php" class="style-btn">Shop Now ➝</a></div>
            <div class="style-card"><span class="style-tag">Mega OFFER</span><h2>Make your New Styles <br />with Our Products</h2><a href="shop-list-view.php" class="style-btn">Shop Now ➝</a></div>
          </div>
        </div>
      </section>
      <section class="best-sell" id="bestSell">
        <div class="container">
          <div class="section-header">
            <h2>Best Sell in this Week</h2>
            <a href="shop-list-view.php" class="view-all">View All</a>
          </div>
          <div class="products-grid best-grid">
            <?= renderGrid($data['best_selling_week']); ?>
          </div>
        </div>
      </section>
      <section class="top-selling" id="topSelling">
        <div class="container">
          <div class="section-header">
            <h2>Top Selling Products</h2>
            <a href="shop-list-view.php" class="view-all">View All</a>
          </div>
          <div class="products-grid top-grid">
            <?= renderGrid($data['top_selling']); ?>
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
          <div class="footer-col"><h4>Contact Info</h4><div class="contact-item"><div class="contact-label">Address:</div><div class="contact-value">64 new heaven market road GSM village Enugu<br />OR Shop 23 Mabel Plaza Onuato Presidential road Enugu</div></div><div class="contact-item"><div class="contact-label">Phone:</div><div class="contact-value">08036634053 || 07017103954<br />09048393201 || 07065756892</div></div></div>
        </div>
      </div>
      <div class="footer-bottom"><div class="container"><div class="copyright">© Royal smart technologies</div></div></div>
    </footer>
    <script src="script.js"></script>
  </body>
</html>
