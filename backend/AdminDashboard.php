<?php
require_once __DIR__.'/auth.php';
require_admin();
require_once __DIR__.'/db.php';
$pdo = db();

// Count active cart items for the badge
$stmt = $pdo->query("SELECT COUNT(*) FROM cart_items");
$cartCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="icon" href="../images/logos/Royal smart logo.jpg" />
    <link rel="stylesheet" href="Dashboard.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <!-- Ensure all resources are loaded before running scripts -->
    <script>
        window.addEventListener('load', function() {
            // Check if resources are loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
            }
        });
    </script>
  </head>
  <body>
    <div class="dashboard">
      <nav class="sidebar">
        <div class="logo">
          <img src="../images/logos/Royal smart logo.jpg" alt="Royal" style="height: 45px; width: 45px; border-radius: 30px;"/>
        </div>
        <ul class="nav-items">
         <a href="AdminDashboard.php"> <li class="active">Dashboard</li></a>
         <a href="product_create.php"> <li>Create</li> </a>
          <a href="products.php">  <li>Tables</li> </a>
          <a href="products.php"> <li>Edit</li> </a>
          <a href="products.php"> <li>Delete <span class="badge">1</span></li> </a>
          <a href="admin_wishlist.php"> <li>Wishlist</li> </a>
          <a href="admin_compare.php"> <li>Compare</li> </a>
          <a href="admin_orders.php"> <li>Orders <span class="badge" style="background:var(--red);border-radius:3px;"><?= $cartCount ?></span></li> </a>
          <li>Charts</li>
        <li>E-Commerce <span class="hot">Hot</span></li>
         <li>Forms</li>
          <li>Blog</li>
          <li>Special Pages</li>
          <li>Documentation</li>
          <li>Multilevel</li>
        </ul>
      </nav>

      <main class="content">
        <div class="stats-grid">
          <div class="stat-card red">
            <h2>914,001</h2>
            <p>VISITS</p>
          </div>
          <div class="stat-card yellow">
            <h2>46.41%</h2>
            <p>BOUNCE RATE</p>
          </div>
          <div class="stat-card green">
            <h2>4,054,876</h2>
            <p>PAGE VIEWS</p>
          </div>
          <div class="stat-card blue">
            <h2>46.43%</h2>
            <p>GROWTH RATE</p>
          </div>
        </div>

        <div class="charts-grid">
          <div class="chart-container">
            <div class="chart-header">
              <h3>User Statistics</h3>
              <div class="toggle"></div>
            </div>
            <canvas id="userStats"></canvas>
          </div>

          <div class="satisfaction-container">
            <h3>CUSTOMER SATISFACTION</h3>
            <div class="satisfaction-rate">
              <h2>93.13%</h2>
              <div class="trend-info">
                <span>Previous: 79.82</span>
                <span class="change">+14.29</span>
              </div>
            </div>
          </div>

          <div class="traffic-container">
            <h3>Visit By Traffic Types</h3>
            <canvas id="trafficPie"></canvas>
          </div>
        </div>
        
        <div class="marketing-grid">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Advertising & Promotion</h3>
                    <div class="toggle"></div>
                </div>
                <canvas id="adStats"></canvas>
            </div>
            
            <div class="social-stats">
                <h3>Social Media Performance</h3>
                <div class="social-grid">
                    <div class="social-card facebook">
                        <i class="material-icons">facebook</i>
                        <div class="stats">
                            <h4>52,869</h4>
                            <p>Followers</p>
                        </div>
                    </div>
                    <div class="social-card x">
                        <span class="x-icon">𝕏</span>
                        <div class="stats">
                            <h4>42,405</h4>
                            <p>Followers</p>
                        </div>
                    </div>
                    <div class="social-card instagram">
                        <i class="material-icons">photo_camera</i>
                        <div class="stats">
                            <h4>35,320</h4>
                            <p>Followers</p>
                        </div>
                    </div>
                    <div class="social-card linkedin">
                        <i class="material-icons">business</i>
                        <div class="stats">
                            <h4>28,458</h4>
                            <p>Connections</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="Dashboard.js"></script>
  </body>
</html>
