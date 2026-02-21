<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Options extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->set_cors_headers();
    }

    private function set_cors_headers() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 3600');
        
        if ($this->input->method() === 'options') {
            exit();
        }
    }

    public function index() {
        echo "OK";
    }
}
