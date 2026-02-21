<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    private $usersFile;

    public function __construct() {
        parent::__construct();
        validate_token();
        $this->usersFile = FCPATH . 'users_dev.json';
    }

    private function getUsers() {
        if (file_exists($this->usersFile)) {
            $content = file_get_contents($this->usersFile);
            return json_decode($content, true) ?: [];
        }
        return [];
    }

    private function saveUsers($users) {
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function index() {
        try {
            if ($this->db_available && isset($this->db)) {
                $users = $this->db->get('users')->result();
                http_response_code(200);
                echo json_encode($users);
                return;
            }
        } catch (Exception $e) {
            // Fall through to file-based
        }

        // File-based fallback
        $users = $this->getUsers();
        http_response_code(200);
        echo json_encode($users);
    }

    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Username dan password harus diisi"]);
            return;
        }

        $data = [
            "id" => count($this->getUsers()) + 1,
            "username" => $input['username'],
            "password" => password_hash($input['password'], PASSWORD_BCRYPT),
            "role" => $input['role'] ?? 'user'
        ];

        try {
            if ($this->db_available && isset($this->db)) {
                $this->db->insert('users', $data);
                http_response_code(201);
                echo json_encode(["status" => true, "message" => "User berhasil dibuat"]);
                return;
            }
        } catch (Exception $e) {
            // Fall through to file-based
        }

        // File-based fallback
        $users = $this->getUsers();
        $users[] = $data;
        $this->saveUsers($users);
        
        http_response_code(201);
        echo json_encode(["status" => true, "message" => "User berhasil dibuat"]);
    }

    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        try {
            if ($this->db_available && isset($this->db)) {
                $this->db->where('id', $id)->update('users', $input);
                http_response_code(200);
                echo json_encode(["status" => true, "message" => "User berhasil diupdate"]);
                return;
            }
        } catch (Exception $e) {
            // Fall through to file-based
        }

        // File-based fallback
        $users = $this->getUsers();
        $updated = false;
        foreach ($users as &$user) {
            if ($user['id'] == $id) {
                $user = array_merge($user, $input);
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveUsers($users);
            http_response_code(200);
            echo json_encode(["status" => true, "message" => "User berhasil diupdate"]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "User tidak ditemukan"]);
        }
    }

    public function delete($id) {
        try {
            if ($this->db_available && isset($this->db)) {
                $this->db->delete('users', ['id' => $id]);
                http_response_code(200);
                echo json_encode(["status" => true, "message" => "User berhasil dihapus"]);
                return;
            }
        } catch (Exception $e) {
            // Fall through to file-based
        }

        // File-based fallback
        $users = $this->getUsers();
        $newUsers = [];
        $deleted = false;
        
        foreach ($users as $user) {
            if ($user['id'] != $id) {
                $newUsers[] = $user;
            } else {
                $deleted = true;
            }
        }

        if ($deleted) {
            $this->saveUsers($newUsers);
            http_response_code(200);
            echo json_encode(["status" => true, "message" => "User berhasil dihapus"]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => false, "message" => "User tidak ditemukan"]);
        }
    }
}