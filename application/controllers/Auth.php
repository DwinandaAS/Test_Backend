<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    private $secret = "SECRET_KEY_API_123";
    private $usersFile;

    public function __construct() {
        parent::__construct();
        // Use file-based storage for development
        $this->usersFile = FCPATH . 'users_dev.json';
    }

    private function getUsers() {
        if (file_exists($this->usersFile)) {
            $content = file_get_contents($this->usersFile);
            return json_decode($content, true) ?: [];
        }
        return [];
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

        // Get user dari database atau file
        $user = null;
        
        // Try database first
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
        
        // Fallback ke file-based users
        if (!$user) {
            $users = $this->getUsers();
            foreach ($users as $u) {
                if ($u['username'] === $username) {
                    $user = (object)$u;
                    break;
                }
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
