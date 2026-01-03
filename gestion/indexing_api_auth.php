<?php

/**
 * Google Indexing API - Authentication Module
 * 
 * Handles OAuth 2.0 authentication using service account credentials.
 * Generates JWT tokens and exchanges them for access tokens.
 * 
 * This module uses only native PHP extensions (openssl, curl, json)
 * and does not require the google-api-php-client library.
 */

/**
 * Get OAuth 2.0 access token for Google Indexing API
 * 
 * @param string|null $serviceAccountPath Path to the service account JSON file
 * @param string|null &$errorMessage Optional reference to store error message
 * @return string|false Access token on success, false on failure
 */
function getIndexingAPIToken($serviceAccountPath = null, &$errorMessage = null)
{
    // Default path if not provided
    if ($serviceAccountPath === null) {
        $serviceAccountPath = __DIR__ . '/meridiano-mbb-4ba1b54b57a9.json';
    }

    // Check if service account file exists
    if (!file_exists($serviceAccountPath)) {
        $errorMessage = "Service account file not found at: $serviceAccountPath";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    // Read and parse service account JSON
    $serviceAccountJson = file_get_contents($serviceAccountPath);
    $serviceAccount = json_decode($serviceAccountJson, true);

    if (!$serviceAccount) {
        $errorMessage = "Failed to parse service account JSON";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    // Validate required fields
    $requiredFields = ['client_email', 'private_key', 'token_uri'];
    foreach ($requiredFields as $field) {
        if (!isset($serviceAccount[$field])) {
            $errorMessage = "Missing required field '$field' in service account JSON";
            error_log("Indexing API Auth Error: $errorMessage");
            return false;
        }
    }

    // Prepare JWT header
    $header = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];

    // Prepare JWT claim set
    $now = time();
    $claimSet = [
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/indexing',
        'aud' => $serviceAccount['token_uri'],
        'iat' => $now,
        'exp' => $now + 3600 // Token valid for 1 hour
    ];

    // Encode header and claim set
    $base64UrlHeader = base64UrlEncode(json_encode($header));
    $base64UrlClaimSet = base64UrlEncode(json_encode($claimSet));

    // Create signature input
    $signatureInput = $base64UrlHeader . '.' . $base64UrlClaimSet;

    // Sign with private key
    $privateKey = $serviceAccount['private_key'];
    $signature = '';

    $key = openssl_pkey_get_private($privateKey);
    if (!$key) {
        $errorMessage = "Failed to load private key";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    $success = openssl_sign($signatureInput, $signature, $key, OPENSSL_ALGO_SHA256);
    openssl_free_key($key);

    if (!$success) {
        $errorMessage = "Failed to sign JWT";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    $base64UrlSignature = base64UrlEncode($signature);

    // Construct JWT
    $jwt = $signatureInput . '.' . $base64UrlSignature;

    // Exchange JWT for access token
    $tokenUrl = $serviceAccount['token_uri'];
    $postData = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        $errorMessage = "CURL error during token exchange: $curlError";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    if ($httpCode !== 200) {
        $errorMessage = "Token exchange failed with HTTP code $httpCode. Response: $response";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    $tokenData = json_decode($response, true);
    if (!$tokenData || !isset($tokenData['access_token'])) {
        $errorMessage = "Invalid token response (missing access_token)";
        error_log("Indexing API Auth Error: $errorMessage");
        return false;
    }

    return $tokenData['access_token'];
}

/**
 * Base64 URL-safe encoding (without padding)
 * 
 * @param string $data Data to encode
 * @return string Encoded data
 */
function base64UrlEncode($data)
{
    $base64 = base64_encode($data);
    $base64Url = strtr($base64, '+/', '-_');
    return rtrim($base64Url, '=');
}
