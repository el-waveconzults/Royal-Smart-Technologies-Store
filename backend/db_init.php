<?php
declare(strict_types=1);

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'RoyalSmart_Store';

$serverDsn = "mysql:host={$host};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($serverDsn, $user, $pass, $options);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$dbName}`");

$pdo->exec("
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(128) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  original_price DECIMAL(12,2) NULL,
  image VARCHAR(255) NOT NULL,
  category_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category_id (category_id),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$insert = $pdo->prepare("INSERT IGNORE INTO categories (slug, name) VALUES (?, ?)");
$categories = [
    ['our_categories', 'Our Categories'],
    ['new_arrivals', 'New Arrivals'],
    ['flash_sale', 'Flash Sale'],
    ['top_selling', 'Top Selling Products'],
    ['best_selling_week', 'Best Sell in this Week'],
];
foreach ($categories as $c) {
    $insert->execute($c);
}

echo 'Categories initialized successfully!';
?>