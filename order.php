<?php
require_once 'db.php';
require_once 'cj_auth.php'; // Pulls your active CJ key function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);

    if (!empty($product_id) && !empty($name) && !empty($address)) {
        try {
            // 1. Record the order securely inside your Aiven MySQL Database
            $stmt = $pdo->prepare("INSERT INTO orders (product_id, customer_name, shipping_address) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $name, $address]);

            // 2. Fetch the dynamic access session token from CJ
            $cj_token = getCJAccessToken();

            $api_response_text = "Token could not be generated. Check your API Key in cj_auth.php.";
            if ($cj_token) {
                // Official CJ v2.0 order endpoint
                $cj_order_url = "https://developers.cjdropshipping.com/api2.0/v1/shopping/order/createOrderV2";
                
                // Structured exactly to match the CJ Developer specs
                $order_payload = json_encode([
                    "orderArea" => "US", // Tell them the main shipping destination region
                    "shippingAddress" => [
                        "firstName" => $name,
                        "lastName" => "StoreCustomer",
                        "addressLine1" => $address,
                        "city" => "New York",     // Standard placeholders required by CJ formatting validation
                        "province" => "NY",
                        "countryCode" => "US",
                        "zip" => "10001",
                        "phone" => "1234567890"
                    ],
                    "products" => [
                        [
                            "sku" => "CJCC224997001AZ", // ⚠️ REPLACE THIS WITH YOUR REAL ACTIVE CJ SKU FROM THE MARKETPLACE!
                            "quantity" => 1
                        ]
                    ]
                ]);

                // Fire outbound cURL call via Render
                $ch = curl_init($cj_order_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $order_payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'CJ-Access-Token: ' . $cj_token
                ]);

                $api_response_text = curl_exec($ch);
                curl_close($ch);
            }
            
            // 3. Display status screen directly to you or the customer for instant debugging
            echo "
            <div style='max-width: 600px; margin: 40px auto; font-family: Arial, sans-serif; padding: 25px; border: 1px solid #ddd; border-radius: 8px; text-align: left; background: #fff;'>
                <h2 style='color: #2ecc71; text-align:center;'>🎉 Checkout Form Submitted</h2>
                <p><strong>Customer Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Database Status:</strong> Order saved successfully in Aiven Cloud.</p>
                
                <div style='background:#f8f9fa; padding:15px; border:1px solid #ccc; margin-top:15px; border-radius:4px; font-family:monospace;'>
                    <strong style='color:#e74c3c;'>🔍 Raw CJ Supplier API Response:</strong>
                    <pre style='white-space: pre-wrap; word-wrap: break-word; margin-top:10px;'>" . htmlspecialchars($api_response_text) . "</pre>
                </div>
                
                <div style='text-align:center; margin-top:20px;'>
                    <a href='index.php' style='color: #007bff; text-decoration: none;'>← Return to Store</a>
                </div>
            </div>";

        } catch (PDOException $e) {
            die("Database save failed: " . $e->getMessage());
        }
    } else {
        echo "Missing required checkout input text.";
    }
} else {
    header("Location: index.php");
    exit;
}
?>
