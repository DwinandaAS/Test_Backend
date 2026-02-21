<?php

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    // Add padding if needed
    $padding = 4 - (strlen($data) % 4);
    if ($padding !== 4) {
        $data .= str_repeat('=', $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode($payload, $secret) {

    $header = json_encode([
        "typ" => "JWT",
        "alg" => "HS256"
    ]);

    $payload = json_encode($payload);

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    $signature = hash_hmac(
        'sha256',
        $base64UrlHeader . "." . $base64UrlPayload,
        $secret,
        true
    );

    $base64UrlSignature = base64UrlEncode($signature);

    return $base64UrlHeader . "." .
           $base64UrlPayload . "." .
           $base64UrlSignature;
}

function jwt_decode($token, $secret) {

    $tokenParts = explode('.', $token);

    if (count($tokenParts) != 3) {
        return false;
    }

    $header = $tokenParts[0];
    $payload = $tokenParts[1];
    $signatureProvided = $tokenParts[2];

    $signature = hash_hmac(
        'sha256',
        $header . "." . $payload,
        $secret,
        true
    );

    $base64UrlSignature = base64UrlEncode($signature);

    if ($base64UrlSignature !== $signatureProvided) {
        return false;
    }

    $payload = json_decode(base64UrlDecode($payload));

    if (isset($payload->exp) && $payload->exp < time()) {
        return false;
    }

    return $payload;
}