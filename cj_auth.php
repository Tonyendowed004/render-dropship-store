
<?php
// cj_auth.php
// CJ Dropshipping API authentication

function getCJAccessToken(): ?string
{
    // Set your API key in a server environment variable.
    $apiKey = 'CJ5942461@api@1c0e45252d5a447bbbc99000d40c94a7';

    if (!$apiKey) {
        error_log('CJ_API_KEY environment variable is missing.');
        return null;
    }

    // CJ Dropshipping API authentication endpoint
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';

    $payload = json_encode([
        'apiKey' => $apiKey
    ]);

    if ($payload === false) {
        error_log('Could not encode CJ authentication payload.');
        return null;
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        error_log('CJ API cURL error: ' . $curlError);
        return null;
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        error_log('CJ API returned invalid JSON. HTTP status: ' . $httpCode);
        return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('CJ API authentication failed. HTTP status: ' . $httpCode);
        return null;
    }

    if (!empty($data['data']['accessToken'])) {
        return $data['data']['accessToken'];
    }

    error_log('CJ API response did not contain an access token.');
    return null;
}
