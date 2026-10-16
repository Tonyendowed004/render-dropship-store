<?php 
require_once 'db.php'; 

// Automatically create database tables if they do not exist
$pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255)
) ENGINE=InnoDB;");

$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    shipping_address TEXT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;");

// Inject a sample tracking watch if empty
if ($pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO products (title, description, price, image_url) VALUES 
    ('Automated Smart Watch', 'Fulfillment API test sample watch.', 49.99, 'https://unsplash.com')");
}

$products = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dropship Store</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fafafa; padding: 40px; text-align: center; }
        .grid { display: flex; justify-content: center; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); width: 280px; }
        .price { color: #27ae60; font-weight: bold; margin: 10px 0; }
        input, textarea, button { width: 100%; padding: 10px; margin: 5px 0; border-radius: 4px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🚀 Live Automated Dropship Store</h1>
    <div class="grid">
        <?php foreach ($products as $p): ?>
        <div class="card">
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <p><?= htmlspecialchars($p['description']) ?></p>
            <div class="price">$<?= number_format($p['price'], 2) ?></div>
            <form action="order.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="text" name="name" placeholder="Your Full Name" required>
                <textarea name="address" placeholder="Complete Shipping Address" required></textarea>
                <button type="submit">Place Order & Sync API</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
