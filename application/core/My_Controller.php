<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $db_available = false;

    public function __construct() {
        parent::__construct();
        $this->set_cors_headers();
        
        // Check if database is available
        try {
            if (isset($this->db) && !empty($this->db)) {
                // Quick test
                $this->db->simple_query("SELECT 1");
                $this->db_available = true;
            }
        } catch (Exception $e) {
            $this->db_available = false;
        }
    }

    protected function set_cors_headers() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');
        header('Access-Control-Max-Age: 3600');
        
        // Handle preflight request BEFORE any output
        if ($this->input->method() === 'options') {
            header('Content-Type: application/json');
            echo json_encode(['status' => true]);
            exit();
        }
        
        // Set JSON content type untuk semua response
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
            header('Content-Type: application/json');
        }
    }
}
