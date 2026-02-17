<?php
// QRCodeGenerator.php - Simple QR Code Generator using Google Charts API
// Lightweight alternative to phpqrcode library

class QRCodeGenerator {
    private $savePath;
    
    public function __construct($savePath = 'public/uploads/qr_codes/') {
        $this->savePath = $savePath;
        
        // Create directory if it doesn't exist
        if (!file_exists($this->savePath)) {
            mkdir($this->savePath, 0777, true);
        }
    }
    
    /**
     * Generate QR code using API and save to file
     * @param string $data - Data to encode in QR
     * @param string $filename - Filename without extension
     * @return string - Path to saved QR code image
     */
    public function generate($data, $filename) {
        // Using quickchart.io API (free, no limits for basic use)
        $size = 300;
        $url = "https://quickchart.io/qr?text=" . urlencode($data) . "&size=" . $size;
        
        // Download QR code image
        $imageData = @file_get_contents($url);
        
        if ($imageData === false) {
            // Fallback: Generate simple data URL based QR
            return $this->generateFallback($data, $filename);
        }
        
        $filepath = $this->savePath . $filename . '.png';
        file_put_contents($filepath, $imageData);
        
        return $filepath;
    }
    
    /**
     * Fallback method using Google Charts API
     */
    private function generateFallback($data, $filename) {
        $size = '300x300';
        $url = "https://chart.googleapis.com/chart?chs={$size}&cht=qr&chl=" . urlencode($data) . "&choe=UTF-8";
        
        $imageData = @file_get_contents($url);
        
        if ($imageData !== false) {
            $filepath = $this->savePath . $filename . '.png';
            file_put_contents($filepath, $imageData);
            return $filepath;
        }
        
        return null;
    }
    
    /**
     * Generate QR code data content for a pass
     */
    public static function getPassQRData($passData) {
        $data = "GATE PASS\n";
        $data .= "Pass#: " . $passData['pass_number'] . "\n";
        $data .= "Name: " . $passData['full_name'] . "\n";
        $data .= "Valid: " . $passData['from_date'] . " to " . $passData['to_date'] . "\n";
        $data .= "Category: " . $passData['category'] . "\n";
        $data .= "Scan URL: " . $_SERVER['HTTP_HOST'] . "/scan-qr.php?pass=" . $passData['pass_number'];
        
        return $data;
    }
}
