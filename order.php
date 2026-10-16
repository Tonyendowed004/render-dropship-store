<?php
require_once 'db.php';
require_once 'cj_auth.php'; // Pulls the dynamic login function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);

    if (!empty($product_id) && !empty($name) && !empty($address)) {
        try {
            // 1. Record the order securely inside your Free Aiven MySQL Database
            $stmt = $pdo->prepare("INSERT INTO orders (product_id, customer_name, shipping_address) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $name, $address]);

            // 2. Fetch the fresh authorization session token dynamically from CJ
            $cj_token = getCJAccessToken();

            $http_code = 0;
            if ($cj_token) {
                // CJ Dropshipping API endpoint to create an un-submitted draft order
                $cj_order_url = "https://cjdropshipping.com";
                
                // Structure the order data according to CJ API specifications
                $order_payload = json_encode([
                    "shippingAddress" => [
                        "firstName" => $name,
                        "lastName" => "Customer", // Can be split if you add a last name field
                        "addressLine1" => $address,
                        "countryCode" => "US",    // Defaulting to US format for verification
                        "phone" => "0000000000"
                    ],
                    "products" => [
                        [
                            "sku" => "CJCC224997001AZ", // We will replace this with a real item code next
                            "quantity" => 1
                        ]
                    ]
                ]);

                // Fire the unrestricted outbound API cURL call via Render
                $ch = curl_init($cj_order_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $order_payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'CJ-Access-Token: ' . $cj_token
                ]);

                $res = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }
            
            // 3. Display success screen to the customer
            echo "
            <div style='max-width: 500px; margin: 50px auto; font-family: Arial; padding: 25px; border: 2px solid #2ecc71; border-radius: 8px; text-align: center;'>
                <h2 style='color: #2ecc71;'>🎉 Order Placed Successfully!</h2>
                <p>Thank you <strong>" . htmlspecialchars($name) . "</strong>. Your shipping routing details were synchronized.</p>
                <p style='font-size:0.8rem; color:#888;'>Fulfillment Sync Status: HTTP " . $http_code . "</p>
                <a href='index.php' style='color: #007bff; text-decoration: none;'>← Return to Store</a>
            </div>";

        } catch (PDOException $e) {
            die("Database sync failed: " . $e->getMessage());
        }
    } else {
        echo "Missing checkout information.";
    }
} else {
    header("Location: index.php");
    exit;
}
?>
