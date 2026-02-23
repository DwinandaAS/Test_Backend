<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends MY_Controller {

    public function __construct() {
        parent::__construct();
        validate_token();
    }

    private function parseCSVData($csvString) {

        if (!$csvString) {
            return [];
        }

        // convert literal \n jadi newline asli
        $csvString = str_replace("\\n", "\n", $csvString);

        // normalize line ending
        $csvString = str_replace(["\r\n", "\r"], "\n", $csvString);

        $rows = explode("\n", $csvString);
        $data = [];

        foreach ($rows as $index => $row) {

            $row = trim($row);

            if ($index == 0 || empty($row)) {
                continue; // skip header
            }

            $fields = explode('|', $row);

            if (count($fields) < 3) {
                continue;
            }

            $nim  = preg_replace('/[^0-9]/', '', $fields[0]);
            $nama = trim($fields[1]);
            $ymd  = preg_replace('/[^0-9]/', '', $fields[2]);

            if ($nim && $nama && $ymd) {
                $data[] = [
                    'NIM' => $nim,
                    'NAMA' => $nama,
                    'YMD' => $ymd
                ];
            }
        }

        return $data;
    }

    private function getExternalData() {

        $url = "https://bit.ly/48ejMhW";

        try {

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 60000,
                    'ignore_errors' => true,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0'
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);

            $result = @file_get_contents($url, false, $context);

            if ($result === false || empty($result)) {
                return [];
            }

            $jsonData = json_decode($result, true);

            if (!$jsonData || !isset($jsonData['DATA'])) {
                return [];
            }

            return $this->parseCSVData($jsonData['DATA']);

        } catch (Exception $e) {
            return [];
        }
    }

    public function name() {

        $data = $this->getExternalData();

        $filtered = array_filter($data, function($row){
            return $row['NAMA'] == "Turner Mia";
        });

        echo json_encode(array_values($filtered));
    }

    public function nim() {

        $data = $this->getExternalData() ?? [];

        $filtered = array_filter($data, function($row){
            return trim($row['NIM']) === "9352078461";
        });

        echo json_encode(array_values($filtered));
    }

    public function ymd() {

        $data = $this->getExternalData();

        $filtered = array_filter($data, function($row){
            return $row['YMD'] == "20230405";
        });

        echo json_encode(array_values($filtered));
    }

    // Debug endpoint untuk melihat semua data yang di-parse
    public function debug() {
        $data = $this->getExternalData();
        echo json_encode([
            'total_records' => count($data),
            'sample_records' => array_slice($data, 0, 5),
            'ymd_20230405_count' => count(array_filter($data, function($row){
                return $row['YMD'] == "20230405";
            }))
        ]);
    }
}