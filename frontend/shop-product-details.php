<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
$pdo = db();
$sessionId = session_id();
function getProductById(PDO $pdo, int $id): ?array {
  $stmt = $pdo->prepare("SELECT id, name, price, original_price, image FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}
function getRelated(PDO $pdo, int $excludeId): array {
  $stmt = $pdo->prepare("SELECT id, name, price, original_price, image FROM products WHERE id <> ? ORDER BY id DESC LIMIT 4");
  $stmt->execute([$excludeId]);
  return $stmt->fetchAll();
}
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {
  $action = $_POST['cart_action'];
  $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid);
  if ($pid > 0) {
    if (!isset($_SESSION['cart'])) {
      $_SESSION['cart'] = [];
    }
    if ($action === 'add') {
      if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart_message'] = 'Item already exists in the cart';
      } else {
        $prod = getProductById($pdo, $pid);
        if ($prod) {
          $_SESSION['cart'][$pid] = [
            'id' => (int)$prod['id'],
            'name' => $prod['name'],
            'price' => (float)$prod['price'],
            'image' => $prod['image'],
            'quantity' => 1,
          ];
          $_SESSION['cart_message'] = '';
          
          // DB Sync
          $stmt = $pdo->prepare("INSERT IGNORE INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, 1)");
          $stmt->execute([$sessionId, $pid]);
        }
      }
      $redirect = 'shop-product-details.php?id=' . $pid;
    } elseif ($action === 'increment') {
      if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['quantity']++;
        
        // DB Sync
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $pid]);
      }
      $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid) . '&cart_open=1';
    } elseif ($action === 'decrement') {
      if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['quantity']--;
        
        // DB Sync
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $pid]);
        
        if ($_SESSION['cart'][$pid]['quantity'] <= 0) {
          unset($_SESSION['cart'][$pid]);
          
          // DB Sync Remove
          $stmt = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?");
          $stmt->execute([$sessionId, $pid]);
        }
      }
      $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid) . '&cart_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wish_action'])) {
  $action = $_POST['wish_action'];
  $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid);
  if ($pid > 0) {
    if (!isset($_SESSION['wishlist'])) {
      $_SESSION['wishlist'] = [];
    }
    if ($action === 'add') {
      if (isset($_SESSION['wishlist'][$pid])) {
        $_SESSION['wish_message'] = 'Item already exists in the wishlist';
      } else {
        $name = isset($_POST['product_name']) ? trim((string)$_POST['product_name']) : '';
        $price = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0.0;
        $image = isset($_POST['product_image']) ? trim((string)$_POST['product_image']) : '';
        $dbId = isset($_POST['product_db_id']) ? (int)$_POST['product_db_id'] : 0;
        if ($name !== '' && $price > 0 && $image !== '') {
          $_SESSION['wishlist'][$pid] = [
            'id' => $pid,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'db_id' => $dbId,
          ];
          $_SESSION['wish_message'] = '';
          
          // DB Sync
          $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (session_id, product_id) VALUES (?, ?)");
          $stmt->execute([$sessionId, $pid]);
        } else {
          $_SESSION['wish_message'] = 'Invalid product data';
        }
      }
      $redirect = 'shop-product-details.php?id=' . $pid;
    } elseif ($action === 'remove') {
      if (isset($_SESSION['wishlist'][$pid])) {
        unset($_SESSION['wishlist'][$pid]);
        
        // DB Sync
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $pid]);
      }
      $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid) . '&wish_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
$compareMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compare_action'])) {
  $action = $_POST['compare_action'];
  $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid);
  if ($pid > 0) {
    if (!isset($_SESSION['compare'])) {
      $_SESSION['compare'] = [];
    }
    if ($action === 'add') {
      if (isset($_SESSION['compare'][$pid])) {
        $_SESSION['compare_message'] = 'Item already exists in compare';
      } else {
        $name = isset($_POST['product_name']) ? trim((string)$_POST['product_name']) : '';
        $price = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0.0;
        $image = isset($_POST['product_image']) ? trim((string)$_POST['product_image']) : '';
        $dbId = isset($_POST['product_db_id']) ? (int)$_POST['product_db_id'] : 0;
        if ($name !== '' && $price > 0 && $image !== '') {
          $_SESSION['compare'][$pid] = [
            'id' => $pid,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'db_id' => $dbId,
          ];
          $_SESSION['compare_message'] = '';
          
          // DB Sync
          $stmt = $pdo->prepare("INSERT IGNORE INTO compare (session_id, product_id) VALUES (?, ?)");
          $stmt->execute([$sessionId, $pid]);
        } else {
          $_SESSION['compare_message'] = 'Invalid product data';
        }
      }
      $redirect = 'shop-product-details.php?id=' . $pid;
    } elseif ($action === 'remove') {
      if (isset($_SESSION['compare'][$pid])) {
        unset($_SESSION['compare'][$pid]);
        
        // DB Sync
        $stmt = $pdo->prepare("DELETE FROM compare WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $pid]);
      }
      $redirect = 'shop-product-details.php?id=' . ($productId > 0 ? $productId : $pid) . '&compare_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
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
$product = $productId > 0 ? getProductById($pdo, $productId) : null;
if (!$product) {
  $firstId = (int)$pdo->query("SELECT id FROM products ORDER BY id ASC LIMIT 1")->fetchColumn();
  if ($firstId) {
    header('Location: shop-product-details.php?id='.$firstId);
    exit;
  }
}
$related = $product ? getRelated($pdo, (int)$product['id']) : [];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Product Details - Royal Smart Technologies</title>
    <link rel="stylesheet" href="styles.css?v=20260120" />
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
  </head>
  <body class="product-details-page">
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
          <a href="shop-product-details.php?id=<?= (int)$product['id'] ?>&wish_open=1" class="icon-btn wish-button">
            ♡
            <span class="cart-badge"<?= $wishCount > 0 ? '' : ' hidden' ?>><?= $wishCount ?></span>
          </a>
          <a href="shop-product-details.php?id=<?= (int)$product['id'] ?>&cart_open=1" class="icon-btn cart-button">
            🛒
            <span class="cart-badge"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= $cartCount ?></span>
          </a>
          <a href="shop-product-details.php?id=<?= (int)$product['id'] ?>&compare_open=1" class="icon-btn compare-button">
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
              <li><a href="#">Shop Product-details</a></li>
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
          <a class="cart-close" href="shop-product-details.php?id=<?= (int)$product['id'] ?>">×</a>
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
    <div class="cart-panel-overlay" id="wishOverlay"<?= $wishOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="wishPanel">
        <div class="cart-header">
          <h2>Your Wishlist</h2>
          <a class="cart-close" href="shop-product-details.php?id=<?= (int)$product['id'] ?>">×</a>
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
          <a class="cart-close" href="shop-product-details.php?id=<?= (int)$product['id'] ?>">×</a>
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
    <main class="product-details">
      <div class="container">
        <div class="pd-wrap">
          <div class="pd-gallery">
            <div class="pd-main">
              <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
            </div>
            <div class="pd-thumbs">
              <img src="<?= htmlspecialchars($product['image']) ?>" alt="" />
              <img src="<?= htmlspecialchars($product['image']) ?>" alt="" />
              <img src="<?= htmlspecialchars($product['image']) ?>" alt="" />
            </div>
          </div>
          <div class="pd-info">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="pd-price">
              <?php if ($product['original_price'] !== null): ?>
              <span class="pd-old">₦<?= number_format((float)$product['original_price']) ?></span>
              <?php endif; ?>
              <span class="pd-new">₦<?= number_format((float)$product['price']) ?></span>
            </div>
            <div class="pd-options">
              <div class="pd-label">Options</div>
              <div class="pd-chips">
                <button>S</button><button>M</button><button>L</button><button>XL</button><button>XXL</button>
              </div>
            </div>
            <div class="pd-actions">
              <form method="post" style="display:inline">
                <input type="hidden" name="cart_action" value="add" />
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>" />
                <button type="submit" class="pd-add">Add to Cart</button>
              </form>
              <form method="post" style="display:inline">
                <input type="hidden" name="wish_action" value="add" />
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>" />
                <input type="hidden" name="product_db_id" value="<?= (int)$product['id'] ?>" />
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" />
                <input type="hidden" name="product_price" value="<?= (float)$product['price'] ?>" />
                <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['image']) ?>" />
                <button type="submit" class="pd-wish">♡ Wishlist</button>
              </form>
            </div>
            <div class="pd-desc">
              <h3>Description & Fit</h3>
              <p>Premium quality device with modern design and reliable performance. Built for everyday use with long-lasting materials and safety features. Ideal for personal, business, and entertainment.</p>
            </div>
            <div class="pd-shipping">
              <h3>Shipping</h3>
              <div class="ship-list">
                <div class="ship-item"><strong>Free</strong> Door Step</div>
                <div class="ship-item"><strong>Pickup</strong> Store Pickup</div>
                <div class="ship-item"><strong>ETA</strong> 4–6 Working Days</div>
              </div>
            </div>
          </div>
        </div>
        <section class="pd-reviews">
          <div class="score-box">
            <div class="big">4.5</div>
            <div class="small">/5</div>
            <div class="sub">(500 New Reviews)</div>
          </div>
          <div class="bars">
            <div class="bar"><span style="width:85%"></span></div>
            <div class="bar"><span style="width:70%"></span></div>
            <div class="bar"><span style="width:50%"></span></div>
            <div class="bar"><span style="width:30%"></span></div>
            <div class="bar"><span style="width:10%"></span></div>
          </div>
          <div class="review-card">
            <div class="review-top">
              <div class="avatar">👤</div>
              <div>
                <div class="name">Alex Mattho</div>
                <div class="date">13 Oct 2024</div>
              </div>
              <div class="stars">★★★★★</div>
            </div>
            <p>Excellent quality and performance. A great choice for everyday use. The design is sleek and the battery life is impressive.</p>
          </div>
        </section>
        <section class="pd-related">
          <h2>You might also like</h2>
          <div class="products-grid">
            <div class="product-card">
              <div class="product-img">
                <img src="../images/homepage-one/product-img/Apple-iPhone-17-Pro-Max-1766416328.webp" alt="Apple iPhone 17 Pro Max" />
                <div class="product-actions">
                  <a href="shop-product-details.php?id=1" title="View Details">⤢</a>
                  <form method="post">
                    <input type="hidden" name="wish_action" value="add" />
                    <input type="hidden" name="product_id" value="1" />
                    <input type="hidden" name="product_db_id" value="1" />
                    <input type="hidden" name="product_name" value="Apple iPhone 17 Pro Max" />
                    <input type="hidden" name="product_price" value="1250000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/Apple-iPhone-17-Pro-Max-1766416328.webp" />
                    <button title="Wishlist">♡</button>
                  </form>
                  <form method="post">
                    <input type="hidden" name="compare_action" value="add" />
                    <input type="hidden" name="product_id" value="1" />
                    <input type="hidden" name="product_db_id" value="1" />
                    <input type="hidden" name="product_name" value="Apple iPhone 17 Pro Max" />
                    <input type="hidden" name="product_price" value="1250000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/Apple-iPhone-17-Pro-Max-1766416328.webp" />
                    <button title="Compare">⇄</button>
                  </form>
                </div>
              </div>
              <div class="product-info">
                <div class="rating">★★★★★</div>
                <h3>Apple iPhone 17 Pro Max</h3>
                <div class="price">
                  <span class="old-price">₦1,350,000</span>
                  <span class="new-price">₦1,250,000</span>
                </div>
                <form method="post">
                  <input type="hidden" name="cart_action" value="add" />
                  <input type="hidden" name="product_id" value="1" />
                  <button type="submit" class="add-to-cart">Add To Cart</button>
                </form>
              </div>
            </div>
            <div class="product-card">
              <div class="product-img">
                <img src="../images/homepage-one/product-img/Dell-XPS-13-ultrabook.jpg" alt="Dell XPS 13 Ultrabook" />
                <div class="product-actions">
                  <a href="shop-product-details.php?id=2" title="View Details">⤢</a>
                  <form method="post">
                    <input type="hidden" name="wish_action" value="add" />
                    <input type="hidden" name="product_id" value="2" />
                    <input type="hidden" name="product_db_id" value="2" />
                    <input type="hidden" name="product_name" value="Dell XPS 13 Ultrabook" />
                    <input type="hidden" name="product_price" value="950000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/Dell-XPS-13-ultrabook.jpg" />
                    <button title="Wishlist">♡</button>
                  </form>
                  <form method="post">
                    <input type="hidden" name="compare_action" value="add" />
                    <input type="hidden" name="product_id" value="2" />
                    <input type="hidden" name="product_db_id" value="2" />
                    <input type="hidden" name="product_name" value="Dell XPS 13 Ultrabook" />
                    <input type="hidden" name="product_price" value="950000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/Dell-XPS-13-ultrabook.jpg" />
                    <button title="Compare">⇄</button>
                  </form>
                </div>
              </div>
              <div class="product-info">
                <div class="rating">★★★★★</div>
                <h3>Dell XPS 13 Ultrabook</h3>
                <div class="price">
                  <span class="old-price">₦1,050,000</span>
                  <span class="new-price">₦950,000</span>
                </div>
                <form method="post">
                  <input type="hidden" name="cart_action" value="add" />
                  <input type="hidden" name="product_id" value="2" />
                  <button type="submit" class="add-to-cart">Add To Cart</button>
                </form>
              </div>
            </div>
            <div class="product-card">
              <div class="product-img">
                <img src="../images/homepage-one/product-img/BeatsStudioBuds.webp" alt="Beats Studio Buds" />
                <div class="product-actions">
                  <a href="shop-product-details.php?id=3" title="View Details">⤢</a>
                  <form method="post">
                    <input type="hidden" name="wish_action" value="add" />
                    <input type="hidden" name="product_id" value="3" />
                    <input type="hidden" name="product_db_id" value="3" />
                    <input type="hidden" name="product_name" value="Beats Studio Buds" />
                    <input type="hidden" name="product_price" value="95000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/BeatsStudioBuds.webp" />
                    <button title="Wishlist">♡</button>
                  </form>
                  <form method="post">
                    <input type="hidden" name="compare_action" value="add" />
                    <input type="hidden" name="product_id" value="3" />
                    <input type="hidden" name="product_db_id" value="3" />
                    <input type="hidden" name="product_name" value="Beats Studio Buds" />
                    <input type="hidden" name="product_price" value="95000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/BeatsStudioBuds.webp" />
                    <button title="Compare">⇄</button>
                  </form>
                </div>
              </div>
              <div class="product-info">
                <div class="rating">★★★★★</div>
                <h3>Beats Studio Buds</h3>
                <div class="price">
                  <span class="old-price">₦105,000</span>
                  <span class="new-price">₦95,000</span>
                </div>
                <form method="post">
                  <input type="hidden" name="cart_action" value="add" />
                  <input type="hidden" name="product_id" value="3" />
                  <button type="submit" class="add-to-cart">Add To Cart</button>
                </form>
              </div>
            </div>
            <div class="product-card">
              <div class="product-img">
                <img src="../images/homepage-one/product-img/Samsung Galaxy_S22_Ultra.jpg" alt="Samsung Galaxy S22 Ultra" />
                <div class="product-actions">
                  <a href="shop-product-details.php?id=4" title="View Details">⤢</a>
                  <form method="post">
                    <input type="hidden" name="wish_action" value="add" />
                    <input type="hidden" name="product_id" value="4" />
                    <input type="hidden" name="product_db_id" value="4" />
                    <input type="hidden" name="product_name" value="Samsung Galaxy S22 Ultra" />
                    <input type="hidden" name="product_price" value="890000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/Samsung Galaxy_S22_Ultra.jpg" />
                    <button title="Wishlist">♡</button>
                  </form>
                  <form method="post">
                    <input type="hidden" name="compare_action" value="add" />
                    <input type="hidden" name="product_id" value="4" />
                    <input type="hidden" name="product_db_id" value="4" />
                    <input type="hidden" name="product_name" value="Samsung Galaxy S22 Ultra" />
                    <input type="hidden" name="product_price" value="890000" />
                    <input type="hidden" name="product_image" value="../images/homepage-one/product-img/Samsung Galaxy_S22_Ultra.jpg" />
                    <button title="Compare">⇄</button>
                  </form>
                </div>
              </div>
              <div class="product-info">
                <div class="rating">★★★★★</div>
                <h3>Samsung Galaxy S22 Ultra</h3>
                <div class="price">
                  <span class="old-price">₦980,000</span>
                  <span class="new-price">₦890,000</span>
                </div>
                <form method="post">
                  <input type="hidden" name="cart_action" value="add" />
                  <input type="hidden" name="product_id" value="4" />
                  <button type="submit" class="add-to-cart">Add To Cart</button>
                </form>
              </div>
            </div>
          </div>
        </section>
      </div>
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
    <script src="script.js?v=20260119"></script>
  </body>
</html>
