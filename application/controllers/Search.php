<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends MY_Controller {

    public function __construct() {
        parent::__construct();
        validate_token();
    }

    private function parseCSVData($csvString) {
        // Normalize line endings
        $csvString = str_replace(["\r\n", "\r"], "\n", $csvString);
        $rows = explode("\n", $csvString);
        $data = [];
        
        // Skip header row (NIM|NAMA|YMD)
        for ($i = 1; $i < count($rows); $i++) {
            $row = trim($rows[$i]);
            if (empty($row)) continue;
            
            $fields = explode('|', $row);
            if (count($fields) >= 3) {
                $nim = trim($fields[0]);
                $nama = trim($fields[1]);
                $ymd = trim($fields[2]);
                
                // Only add if all fields have values
                if (!empty($nim) && !empty($nama) && !empty($ymd)) {
                    $data[] = [
                        'NIM' => $nim,
                        'NAMA' => $nama,
                        'YMD' => $ymd
                    ];
                }
            }
        }
        
        return $data;
    }

    private function getExternalData() {

        // Cache file untuk fallback saat API tidak tersedia
        $cacheFile = FCPATH . 'external_data_cache.json';
        $cacheExpiration = 3600; // 1 jam
        
        // Check if cache adalah fresh (less than 1 hour old)
        if (file_exists($cacheFile)) {
            $cacheAge = time() - filemtime($cacheFile);
            if ($cacheAge < $cacheExpiration) {
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                if ($cachedData && is_array($cachedData) && !empty($cachedData)) {
                    return $cachedData;
                }
            }
        }
        
        // Try to fetch fresh data from API
        try {
            $url = "https://bit.ly/48ejMhW";

            // Enable allow_url_fopen temporarily if needed
            $originalSetting = ini_get('allow_url_fopen');
            if (!$originalSetting) {
                ini_set('allow_url_fopen', '1');
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'user_agent' => 'Mozilla/5.0'
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);

            $result = @file_get_contents($url, false, $context);

            // Restore original setting
            if (!$originalSetting) {
                ini_set('allow_url_fopen', '0');
            }

            if ($result !== false && !empty($result)) {
                $jsonData = json_decode($result, true);
                if ($jsonData && isset($jsonData['RC']) && $jsonData['RC'] == 200 && isset($jsonData['DATA'])) {
                    // Parse CSV data dari field DATA
                    $parsedData = $this->parseCSVData($jsonData['DATA']);
                    if (!empty($parsedData)) {
                        // Update cache dengan data baru
                        file_put_contents($cacheFile, json_encode($parsedData));
                        return $parsedData;
                    }
                }
            }
        } catch (Exception $e) {
            // Fallback to cache
        }

        if (file_exists($cacheFile)) {
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            if ($cachedData && is_array($cachedData) && !empty($cachedData)) {
                return $cachedData;
            }
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

        $data = $this->getExternalData();

        $filtered = array_filter($data, function($row){
            return $row['NIM'] == "9352078461";
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