<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    private $secret = "SECRET_KEY_API_123";

    public function __construct() {
        parent::__construct();
    }

    public function login() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!is_array($input)) {
            $input = [];
        }

        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        // Validation
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                "status" => false,
                "message" => "Username dan password harus diisi"
            ]);
            return;
        }

        // Get user dari database
        $user = null;
        
        if ($this->db_available && isset($this->db)) {
            try {
                $user = $this->db
                    ->where('username', $username)
                    ->get('users')
                    ->row();
            } catch (Exception $e) {
                $user = null;
            }
        }

        if (!$user || !password_verify($password, $user->password ?? '')) {
            http_response_code(401);
            echo json_encode([
                "status" => false,
                "message" => "Username atau password salah"
            ]);
            return;
        }

        $payload = [
            "id" => $user->id ?? 1,
            "username" => $user->username,
            "role" => $user->role ?? 'user',
            "iat" => time(),
            "exp" => time() + 3600
        ];

        $token = jwt_encode($payload, $this->secret);

        http_response_code(200);
        echo json_encode([
            "status" => true,
            "message" => "Login berhasil",
            "token" => $token
        ]);
    }
}
