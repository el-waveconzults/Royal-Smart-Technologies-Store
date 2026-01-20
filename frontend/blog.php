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
  $redirect = 'blog.php';
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
      $redirect = 'blog.php';
    } elseif ($action === 'increment') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
      }
      $redirect = 'blog.php?cart_open=1';
    } elseif ($action === 'decrement') {
      if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']--;
        if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
          unset($_SESSION['cart'][$productId]);
        }
      }
      $redirect = 'blog.php?cart_open=1';
    }
  }
  header('Location: '.$redirect);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wish_action'])) {
  $action = $_POST['wish_action'];
  $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
  $redirect = 'blog.php';
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
        } else {
          $_SESSION['wish_message'] = 'Invalid product data';
        }
      }
      $redirect = 'blog.php';
    } elseif ($action === 'remove') {
      if (isset($_SESSION['wishlist'][$productId])) {
        unset($_SESSION['wishlist'][$productId]);
      }
      $redirect = 'blog.php?wish_open=1';
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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Blog - Royal Smart Technologies</title>
    <link rel="stylesheet" href="styles.css?v=20260119" />
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
  </head>
  <body class="blog-page">
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
          <a href="blog.php?wish_open=1" class="icon-btn wish-button">
            ♡
            <span class="cart-badge"<?= $wishCount > 0 ? '' : ' hidden' ?>><?= $wishCount ?></span>
          </a>
          <a href="blog.php?cart_open=1" class="icon-btn cart-button">
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
              <li><a href="blog.php" aria-current="page">Blog</a></li>
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
          <a class="cart-close" href="blog.php">×</a>
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
          <a class="cart-close" href="blog.php">×</a>
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
    <main class="blog-main">
      <section class="blog-hero">
        <div class="container">
          <div class="blog-hero-card">
            <div class="blog-hero-left">
              <h1>Royal Smart Blog</h1>
              <p>The best tips, news and insights about gadgets and smart tech.</p>
            </div>
            <div class="blog-hero-right">
              <div class="blog-search">
                <input type="text" placeholder="Search articles (AI, laptops, phones...)" />
                <button>Search</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="blog-latest">
        <div class="container">
          <div class="blog-top-grid">
            <div class="latest-post">
              <div class="post-cover"><img src="../images/homepage-one/slide image card two.webp" alt="Latest" /></div>
              <div class="post-content">
                <div class="post-meta">By Royal Team • 7 min read</div>
                <h2>Transforming B2B Marketing in the Face of AI</h2>
                <p>How AI reshapes marketing strategies, tools and customer journeys with real-world examples and practical frameworks.</p>
              </div>
            </div>
            <aside class="editors-picks">
              <h4>Editor’s Picks</h4>
              <ul>
                <li><img src="../images/homepage-one/product-img/Samsung Galaxy_S22_Ultra.jpg" alt="" /><span>Surviving Tough Times: The Budget Gadgets Marketing Checklist</span></li>
                <li><img src="../images/homepage-one/product-img/Dell-XPS-13-ultrabook.jpg" alt="" /><span>Overcoming Complacency: How Challenges Can Drive Innovation</span></li>
                <li><img src="../images/homepage-one/product-img/ps4 controller.webp" alt="" /><span>Elevate Your Hotel and Travel Marketing with Smart Devices</span></li>
                <li><img src="../images/homepage-one/product-img/BeatsStudioBuds.webp" alt="" /><span>Best Alternatives for Premium Sound on a Budget</span></li>
              </ul>
            </aside>
          </div>
        </div>
      </section>
      <section class="blog-grid-section">
        <div class="container">
          <div class="blog-filters">
            <button class="filter-btn">All</button>
            <button class="filter-btn">Guides</button>
            <button class="filter-btn">Reviews</button>
            <button class="filter-btn">Tips</button>
            <div class="search-inline">
              <input type="text" placeholder="Search" />
            </div>
          </div>
          <div class="blog-grid">
            <article class="post-card">
              <div class="thumb"><img src="../images/homepage-one/product-img/oppo-reno-10-pro.webp" alt="" /></div>
              <div class="info">
                <h3>7 Alternatives for Social Media Monitoring</h3>
                <p>Tools and workflows to track brand mentions and trends across networks.</p>
                <div class="meta">Guide • 6 min read</div>
              </div>
            </article>
            <article class="post-card">
              <div class="thumb"><img src="../images/homepage-one/product-img/Creative-Wireless-Bone-Conduction-Headphones-with-Bluetooth-5_3-11.webp" alt="" /></div>
              <div class="info">
                <h3>Future-Proof Your Brand’s Marketing with Email and Social Media</h3>
                <p>Build durable channels that drive compounding results over time.</p>
                <div class="meta">Strategy • 8 min read</div>
              </div>
            </article>
            <article class="post-card">
              <div class="thumb"><img src="../images/homepage-one/product-img/Apple-iPhone-17-Pro-Max-1766416328.webp" alt="" /></div>
              <div class="info">
                <h3>Unplugged: Real Challenges, Real Solutions</h3>
                <p>Field notes from deployments and how teams solved unexpected issues.</p>
                <div class="meta">Case Study • 5 min read</div>
              </div>
            </article>
            <article class="post-card signup-card">
              <div class="signup-inner">
                <h3>Keep up to date with smart tech marketing!</h3>
                <form>
                  <div class="row">
                    <input placeholder="First name" />
                    <input placeholder="Last name" />
                  </div>
                  <div class="row">
                    <input placeholder="Email" />
                    <input placeholder="Phone" />
                  </div>
                  <button type="button">Sign up</button>
                </form>
              </div>
            </article>
            <article class="post-card">
              <div class="thumb"><img src="../images/homepage-one/product-img/samsung galaxy z fold 6.jpg" alt="" /></div>
              <div class="info">
                <h3>How Hospitality Marketing Changed Since the Pandemic</h3>
                <p>Trends shaping customer behavior and how to adapt your messaging.</p>
                <div class="meta">Research • 9 min read</div>
              </div>
            </article>
            <article class="post-card">
              <div class="thumb"><img src="../images/homepage-one/product-img/bluetooth-headphones-1766416865.webp" alt="" /></div>
              <div class="info">
                <h3>How to Create Better Customer Experiences in 2024</h3>
                <p>Blueprints for onboarding, support, and post-purchase delight.</p>
                <div class="meta">CX • 7 min read</div>
              </div>
            </article>
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
    <script src="script.js?v=20260119"></script>
  </body>
</html>
