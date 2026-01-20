<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
$pdo = db();
function getProductById(PDO $pdo, int $id): ?array {
  $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {
  $action = $_POST['cart_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'about.php';
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
        }
      }
    } elseif ($action === 'increment') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
      }
      $redirect = 'about.php?cart_open=1';
    } elseif ($action === 'decrement') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']--;
        if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
          unset($_SESSION['cart'][$productId]);
        }
      }
      $redirect = 'about.php?cart_open=1';
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
$productsCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$categoriesCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>About Us - Royal Smart Technologies</title>
    <link rel="stylesheet" href="styles.css?v=20260118" />
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
  </head>
  <body class="about-page">
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
          <button class="icon-btn">♡</button>
          <a href="about.php?cart_open=1" class="icon-btn cart-button">
            🛒
            <span class="cart-badge"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= $cartCount ?></span>
          </a>
          <button class="icon-btn">⇄</button>
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
          <li>
            <a href="#">Shop ▾</a>
            <ul class="dropdown">
              <li><a href="shop-list-view.php">Shop List View</a></li>
              <li><a href="shop-product-details.php">Shop Product-details</a></li>
            </ul>
          </li>
          <li>
            <a href="#">Pages ▾</a>
            <ul class="dropdown">
              <li><a href="blog.php">Blog</a></li>
              <li><a href="faq.php">FAQ</a></li>
            </ul>
          </li>
          <li><a href="about.php" aria-current="page">About</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </nav>
    </header>
    <div class="cart-panel-overlay" id="cartOverlay"<?= $cartOpen ? '' : ' hidden' ?>>
      <aside class="cart-panel" id="cartPanel">
        <div class="cart-header">
          <h2>Your Cart</h2>
          <a class="cart-close" href="about.php">×</a>
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
    
<!-- 1. Trust Features -->
<section class="features-section">
  <div class="container2">
    <h2 class="section-title">Explore Our E-Commerce Store</h2>

    <div class="features-grid">
      <div class="feature-card">
        <img src="../images/about/guarantee image.avif" alt="Genuine Guaranteed" onerror="this.src='../images/homepage-one/slide image card one.jpg'">
        <div class="feature-overlay">
          <h3>Genuine Guaranteed</h3>
        </div>
        <div class="plus-btn">+</div>
      </div>

      <div class="feature-card">
        <img src="../images/about/secure image.webp" alt="Secured Payment Gateway" onerror="this.src='../images/homepage-one/slide image card three.jpg'">
        <div class="feature-overlay">
          <h3>Secured Payment Gateway</h3>
        </div>
        <div class="plus-btn">+</div>
      </div>

      <div class="feature-card">
        <img src="../images/about/Return-Policy image.png" alt="Free Return &amp; Refund" onerror="this.src='../images/homepage-one/Apple-iPhone-Air-Sky-Blue.webp'">
        <div class="feature-overlay">
          <h3>Free Return & Refund</h3>
        </div>
        <div class="plus-btn">+</div>
      </div>

      <div class="feature-card">
        <img src="../images/about/delivery image.avif" alt="Worldwide Delivery" onerror="this.src='../images/homepage-one/phone-slider.jpg'">
        <div class="feature-overlay">
          <h3>Worldwide Delivery</h3>
        </div>
        <div class="plus-btn">+</div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Reviews -->
<section class="reviews-section">
  <div class="container">
    <div class="reviews-grid">
      <div class="big-score">
        <div class="score">4.9</div>
        <div class="based-on">Based on 5000+ reviews</div>
      </div>

      <div class="reviews-track">
      <div class="review-card review-item">
        <div class="stars">★★★★★</div>
        <blockquote>"Great Price & Services. I have been going to this store <br> for almost four years now, and have always received great service and fair prices..."</blockquote>
        <div class="reviewer">Paul Eden <small>— via Google Reviews</small></div>
      </div>

      <div class="review-card review-item">
        <div class="stars">★★★★★</div>
        <blockquote>"Great Price & Services. They always go out of their way to finish <br> the work on time, and if it's very busy they will rent a car..."</blockquote>
        <div class="reviewer">Tochukwu Eze <small>— via Google Reviews</small></div>
      </div>

      <div class="review-card review-item">
        <div class="stars">★★★★★</div>
        <blockquote>"Great Price & Services. Excellent communication and very professional team. <br> Highly recommend!"</blockquote>
        <div class="reviewer">Vera Bonasine <small>— via Google Reviews</small></div>
      </div>

      <div class="review-card review-item">
        <div class="stars">★★★★★</div>
        <blockquote>"Fast delivery and genuine products. My go-to store for gadgets."</blockquote>
        <div class="reviewer">Chinedu Obi <small>— via Google Reviews</small></div>
      </div>

      <div class="review-card review-item">
        <div class="stars">★★★★★</div>
        <blockquote>"Support team resolved my issue quickly. Very professional."</blockquote>
        <div class="reviewer">Grace Okon <small>— via Google Reviews</small></div>
      </div>

      <div class="review-card review-item">
        <div class="stars">★★★★☆</div>
        <blockquote>"Great prices, secure payment, and smooth ordering experience."</blockquote>
        <div class="reviewer">Ibrahim Musa <small>— via Google Reviews</small></div>
      </div>

      <div class="review-card review-item">
        <div class="stars">★★★★★</div>
        <blockquote>"Warranty honored without stress. Highly recommend this store."</blockquote>
        <div class="reviewer">Ngozi A. <small>— via Google Reviews</small></div>
      </div>
      </div>
    </div>
  </div>
</section>

<!-- 3. Trending Tags + Bottom Badges -->
<section class="tags-section">
  <div class="container">
    <h2 class="trending-title">TRENDING TAGS</h2>

    <div class="tag-cloud">
      <span class="tag active">Samsung</span>
      <span class="tag">Apple</span>
      <span class="tag">iPhone 15 Pro Max</span>
      <span class="tag">MacBook Pro</span>
      <span class="tag">Apple AirPods Pro</span>
      <span class="tag">Marshall Stanmore</span>
      <span class="tag">Gaming Headset</span>
      <span class="tag">Game Console</span>
      <span class="tag">Xiaomi</span>
      <span class="tag">Huawei MateView</span>
      <span class="tag">Dell Alienware</span>
      <span class="tag">Samsung Galaxy Watch</span>
      <span class="tag">Garmin Sport Watches</span>
    </div>

    <div class="bottom-badges">
      <div class="badge-item">
        <i class="fas fa-truck"></i>
        <h4>FREE US DELIVERY</h4>
        <p>For US customers (including Alaska and Hawaii) or orders over $200</p>
      </div>

      <div class="badge-item">
        <i class="fas fa-shield-alt"></i>
        <h4>SECURE PAYMENT</h4>
        <p>We accept Visa, American Express, PayPal, Mastercard and Discover</p>
      </div>

      <div class="badge-item">
        <i class="fas fa-medal"></i>
        <h4>3 YEAR WARRANTY</h4>
        <p>All of our products are made with care and covered for one year against manufacturing defects</p>
      </div>

      <div class="badge-item">
        <i class="fas fa-headset"></i>
        <h4>SUPPORT 24/7</h4>
        <p>Contact us 24 hours, 7 days a week<br>Call Us: 08036634053</p>
      </div>
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
    <script src="script.js?v=20260118"></script>
  </body>
</html>
