
<?php
// cj_auth.php
// CJ Dropshipping API authentication

function getCJAccessToken(): ?string
{
    // Configure CJ_API_KEY in your server environment.
    $apiKey = getenv('CJ5942461@api@1c0e45252d5a447bbbc99000d40c94a7');

    if ($apiKey === false || trim($apiKey) === '') {
        error_log('CJ authentication: CJ_API_KEY is not configured.');
        return null;
    }

    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';

    $payload = json_encode([
        'apiKey' => trim($apiKey)
    ]);

    if ($payload === false) {
        error_log('CJ authentication: Could not encode request.');
        return null;
    }

    $ch = curl_init($url);

    if ($ch === false) {
        error_log('CJ authentication: Could not initialize cURL.');
        return null;
    }

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
        error_log('CJ authentication cURL error: ' . $curlError);
        return null;
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        error_log('CJ authentication: Invalid JSON response. HTTP ' . $httpCode);
        return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('CJ authentication failed. HTTP ' . $httpCode);
        return null;
    }

    if (
        isset($data['data']['accessToken']) &&
        is_string($data['data']['accessToken']) &&
        $data['data']['accessToken'] !== ''
    ) {
        return $data['data']['accessToken'];
    }

    error_log('CJ authentication: Access token missing from response.');
    return null;
}
