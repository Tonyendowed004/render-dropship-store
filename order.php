<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);

    if (!empty($product_id) && !empty($name) && !empty($address)) {
        try {
            // 1. Record order inside free Aiven DB
            $stmt = $pdo->prepare("INSERT INTO orders (product_id, customer_name, shipping_address) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $name, $address]);

            // 2. Automated outbound API cURL trigger (Allowed on Render Free)
            $supplier_url = "https://supplier.com"; // Replace with your supplier endpoint
            $payload = json_encode([
                "api_key" => "YOUR_KEY",
                "product" => $product_id,
                "shipping" => ["name" => $name, "address" => $address]
            ]);

            $ch = curl_init($supplier_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $res = curl_exec($ch);
            curl_close($ch);

            echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>
                    <h2>🎉 Success! Order routed to Supplier API</h2>
                    <a href='index.php'>Return to Store</a>
                  </div>";

        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    }
}
?>
