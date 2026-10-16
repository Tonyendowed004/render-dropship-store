<?php
// cj_auth.php - Manages the dynamic login session token for CJ Dropshipping

function getCJAccessToken() {
    $url = "https://cjdropshipping.com";
    
    // ⚠️ PASTE YOUR RECENTLY GENERATED CJ API KEY HERE
    $apiKey = "CJ5942461@api@1c0e45252d5a447bbbc99000d40c94a7"; 

    $payload = json_encode([
        "apiKey" => $apiKey
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // Pulls the dynamic session token to bypass firewall checks
    if (isset($data['data']['accessToken'])) {
        return $data['data']['accessToken'];
    }

    return null;
}
?>
