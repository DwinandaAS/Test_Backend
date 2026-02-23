<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function __construct() {
        parent::__construct();
        validate_token();
    }

    public function index() {
        if ($this->db_available && isset($this->db)) {
            $users = $this->db->get('users')->result();
            http_response_code(200);
            echo json_encode($users);
            return;
        }

        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Database tidak tersedia"]);
    }

    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "Username dan password harus diisi"]);
            return;
        }

        $data = [
            "username" => $input['username'],
            "password" => password_hash($input['password'], PASSWORD_BCRYPT),
            "role" => $input['role'] ?? 'user'
        ];

        if ($this->db_available && isset($this->db)) {
            $this->db->insert('users', $data);
            http_response_code(201);
            echo json_encode(["status" => true, "message" => "User berhasil dibuat"]);
            return;
        }

        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Database tidak tersedia"]);
    }

    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if ($this->db_available && isset($this->db)) {
            $this->db->where('id', $id)->update('users', $input);
            http_response_code(200);
            echo json_encode(["status" => true, "message" => "User berhasil diupdate"]);
            return;
        }

        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Database tidak tersedia"]);
    }

    public function delete($id) {
        if ($this->db_available && isset($this->db)) {
            $this->db->delete('users', ['id' => $id]);
            http_response_code(200);
            echo json_encode(["status" => true, "message" => "User berhasil dihapus"]);
            return;
        }

        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Database tidak tersedia"]);
    }
}