<?php
require_once __DIR__ . '/../backend/db.php';
$pdo = db();
function getProducts(PDO $pdo, string $slug): array {
  $stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.original_price, p.image FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE c.slug = ? ORDER BY p.id DESC");
  $stmt->execute([$slug]);
  return $stmt->fetchAll();
}
$data = [
  'new_arrivals' => getProducts($pdo, 'new_arrivals'),
  'flash_sale' => getProducts($pdo, 'flash_sale'),
  'top_selling' => getProducts($pdo, 'top_selling'),
  'best_selling_week' => getProducts($pdo, 'best_selling_week'),
];
function renderGrid(array $items): string {
  $html = '';
  foreach ($items as $p) {
    $old = $p['original_price'];
    $html .= '<div class="product-card"><div class="product-img"><img src="'.htmlspecialchars($p['image']).'" alt="'.htmlspecialchars($p['name']).'" /><div class="product-actions"><button title="View Details">⤢</button><button title="Wishlist">♡</button><button title="Compare">⇄</button></div></div><div class="product-info"><div class="rating">★★★★★</div><h3>'.htmlspecialchars($p['name']).'</h3><div class="price">'.($old !== null ? '<span class="old-price">₦'.number_format((float)$old).'</span>' : '').'<span class="new-price">₦'.number_format((float)$p['price']).'</span></div><button class="add-to-cart">Add To Cart</button></div></div>';
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
    <link rel="stylesheet" href="styles.css" />
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
          <button class="icon-btn">♡</button>
          <button class="icon-btn">🛒</button>
          <button class="icon-btn">👤</button>
        </div>
      </div>
      <nav class="main-nav">
        <div class="categories-wrapper">
          <button class="categories-toggle" id="categoriesToggle">☰ All Categories ▾</button>
          <aside class="categories-panel" id="categoriesPanel" aria-hidden="true">
            <ul>
              <li>Phones ▸
                <ul>
                  <li>Iphone</li>
                  <li>Samsung</li>
                </ul>
              </li>
              <li>Laptops ▸
                <ul>
                  <li>Macbook</li>
                  <li>HP laptops</li>
                </ul>
              </li>
              <li>Software Accessories</li>
              <li>Tablet / iPad</li>
              <li>Chargers</li>
            </ul>
          </aside>
        </div>
        <ul class="nav-links">
          <li><a href="#">Home</a></li>
          <li>
            <a href="#">Shop ▾</a>
            <ul class="dropdown">
              <li><a href="#">Shop List View</a></li>
              <li><a href="#">Shop Category Icon</a></li>
            </ul>
          </li>
          <li>
            <a href="#">Pages ▾</a>
            <ul class="dropdown">
              <li><a href="#">Product-details</a></li>
              <li><a href="#">FAQ</a></li>
            </ul>
          </li>
          <li><a href="#">About</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </nav>
    </header>
    <main>
      <section class="hero">
        <div class="carousel" id="carousel">
          <div class="slide active" style="background-image: url('../images/homepage-one/slide image card one.jpg');">
            <div class="caption dark-text">
              <h2>Welcome</h2>
              <h1>To Royal Smart Technologies</h1>
              <p>Your No.1 Plug for premium Gadgets & Smart Devices.</p>
              <a class="btn" href="#">Go Shopping ▸</a>
            </div>
          </div>
          <div class="slide" style="background-image: url('../images/homepage-one/slide image card two.webp');">
            <div class="caption dark-text">
              <h1>Royal Smart Technologies</h1>
              <p>Smart Choices. Royal Quality. Order today — get it delivered to your doorstep.</p>
              <a class="btn" href="#">Browse Collection ▸</a>
            </div>
          </div>
          <div class="slide" style="background-image: url('../images/homepage-one/slide image card three.jpg');">
            <div class="caption">
              <h2>Latest Devices</h2>
              <h1>Discover the newest phones at competitive prices.</h1>
              <p>-100% Authentic</p>
              <a class="btn" href="#">Shop Devices ▸</a>
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
            <a href="#" class="view-all">View All</a>
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
      <section class="discount-banners">
        <div class="container">
          <div class="banners-grid">
            <div class="banner-item" style="background-image: url('../images/homepage-one/discount-1.jpg');">
              <div class="banner-content">
                <span class="banner-tag">New Style</span>
                <h2>Get <span class="highlight">65% Offer</span><br />& Make New<br />Fusion.</h2>
                <a href="#" class="shop-now-btn">Shop Now ➝</a>
              </div>
            </div>
            <div class="banner-item" style="background-image: url('../images/homepage-one/discount-2.jpg');">
              <div class="banner-content">
                <span class="banner-tag">New Style</span>
                <h2>Get <span class="highlight">65% Offer</span><br />& Make New<br />Fusion.</h2>
                <a href="#" class="shop-now-btn">Shop Now ➝</a>
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
            <a href="#" class="view-all">View All</a>
          </div>
          <div class="products-grid flash-grid">
            <?= renderGrid($data['flash_sale']); ?>
          </div>
        </div>
      </section>
      <section class="new-arrivals" id="newArrivals">
        <div class="container">
          <div class="section-header">
            <h2>NEW ARRIVALS</h2>
            <a href="#" class="view-all">View All</a>
          </div>
          <div class="products-grid">
            <?= renderGrid($data['new_arrivals']); ?>
          </div>
        </div>
      </section>
      <section class="best-sell" id="bestSell">
        <div class="container">
          <div class="section-header">
            <h2>Best Sell in this Week</h2>
            <a href="#" class="view-all">View All</a>
          </div>
          <div class="products-grid best-grid">
            <?= renderGrid($data['best_selling_week']); ?>
          </div>
        </div>
      </section>
      <section class="style-offers" id="styleOffers">
        <div class="container">
          <div class="style-grid">
            <div class="style-card-one"><span class="style-tag">NEW STYLE</span><h2>Get 65% Offer & Make New Fusion.</h2><a href="#" class="style-btn">Shop Now ➝</a></div>
            <div class="style-card"><span class="style-tag">Mega OFFER</span><h2>Make your New Styles <br />with Our Products</h2><a href="#" class="style-btn">Shop Now ➝</a></div>
          </div>
        </div>
      </section>
      <section class="top-selling" id="topSelling">
        <div class="container">
          <div class="section-header">
            <h2>Top Selling Products</h2>
            <a href="#" class="view-all">View All</a>
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
