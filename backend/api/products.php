<?php
declare(strict_types=1);
require_once __DIR__.'/../db.php';
$pdo = db();
header('Content-Type: application/json');
$rows = $pdo->query("
  SELECT p.id, p.name, p.price, p.original_price, p.image, c.slug
  FROM products p
  LEFT JOIN categories c ON c.id = p.category_id
  ORDER BY p.id DESC
")->fetchAll();
$out = [
  'new_arrivals' => [],
  'flash_sale' => [],
  'top_selling' => [],
  'best_selling_week' => [],
];
foreach ($rows as $r) {
  $slug = $r['slug'] ?? null;
  if ($slug && isset($out[$slug])) {
    $out[$slug][] = [
      'id' => (int)$r['id'],
      'name' => $r['name'],
      'price' => (float)$r['price'],
      'original_price' => $r['original_price'] !== null ? (float)$r['original_price'] : null,
      'image' => $r['image'],
    ];
  }
}
echo json_encode($out);
