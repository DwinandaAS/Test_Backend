<?php

function validate_token() {

    $CI =& get_instance();
    $secret = "SECRET_KEY_API_123";

    $headers = $CI->input->request_headers();

    // Check Authorization header (case-insensitive)
    $authHeader = null;
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = $value;
            break;
        }
    }

    if (!$authHeader) {
        unauthorized();
    }

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        unauthorized();
    }

    $token = $matches[1];

    $decoded = jwt_decode($token, $secret);

    if (!$decoded) {
        unauthorized();
    }

    return $decoded;
}

function unauthorized() {
    http_response_code(401);
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}