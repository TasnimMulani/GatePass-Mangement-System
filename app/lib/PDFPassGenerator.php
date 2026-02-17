<?php
// PDFPassGenerator.php - Simple PDF Badge Generator

class PDFPassGenerator {
    
    /**
     * Generate a simple PDF pass badge
     * @param array $passData - Pass details
     * @return string - Path to generated PDF
     */
    public function generatePass($passData) {
        // For simplicity, we'll create an HTML version that can be printed
        // For production, integrate FPDF or mPDF library
        
        $html = $this->getPassHTMLTemplate($passData);
        
        // Save as HTML file (can be opened in browser and printed)
        $filename = 'pass_' . $passData['pass_number'] . '.html';
        $filepath = 'public/uploads/passes/' . $filename;
        
        // Create directory if not exists
        if (!file_exists('public/uploads/passes/')) {
            mkdir('public/uploads/passes/', 0777, true);
        }
        
        file_put_contents($filepath, $html);
        
        return $filepath;
    }
    
    /**
     * Get HTML template for printable pass
     */
    private function getPassHTMLTemplate($passData) {
        $qrCodePath = isset($passData['qr_code_path']) ? $passData['qr_code_path'] : '';
        $photoPath = isset($passData['photo_path']) ? $passData['photo_path'] : '';
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gate Pass - ' . htmlspecialchars($passData['pass_number']) . '</title>
    <style>
        @page { size: 3.375in 2.125in; margin: 0; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        .badge {
            width: 3.375in;
            height: 2.125in;
            border: 2px solid #6366f1;
            border-radius: 8px;
            padding: 10px;
            box-sizing: border-box;
            position: relative;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        }
        .header {
            text-align: center;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 5px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
        }
        .content {
            display: flex;
            gap: 10px;
        }
        .photo {
            width: 80px;
            height: 100px;
            background: #e0e0e0;
            border: 1px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
        }
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .details {
            flex: 1;
            font-size: 10px;
        }
        .detail-row {
            margin-bottom: 3px;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .qr-code {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 60px;
            height: 60px;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 5px;
        }
        .print-btn {
            margin: 20px;
            padding: 10px 20px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print Badge</button>
    </div>
    
    <div class="badge">
        <div class="header">
            <h2>GATE PASS</h2>
        </div>
        
        <div class="content">
            <div class="photo">';
            
        if (!empty($photoPath) && file_exists($photoPath)) {
            $html .= '<img src="' . $photoPath . '" alt="Photo">';
        } else {
            $html .= '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999;">No Photo</div>';
        }
        
        $html .= '</div>
            <div class="details">
                <div class="detail-row">
                    <span class="label">Pass #:</span> ' . htmlspecialchars($passData['pass_number']) . '
                </div>
                <div class="detail-row">
                    <span class="label">Name:</span> ' . htmlspecialchars($passData['full_name']) . '
                </div>
                <div class="detail-row">
                    <span class="label">Category:</span> ' . htmlspecialchars($passData['category']) . '
                </div>
                <div class="detail-row">
                    <span class="label">ID:</span> ' . htmlspecialchars($passData['identity_card_no']) . '
                </div>
                <div class="detail-row">
                    <span class="label">Valid:</span> ' . date('d/m/Y', strtotime($passData['from_date'])) . ' - ' . date('d/m/Y', strtotime($passData['to_date'])) . '
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span> <span style="color: #10b981; font-weight: bold;">' . htmlspecialchars($passData['status']) . '</span>
                </div>
            </div>
        </div>';
        
        if (!empty($qrCodePath) && file_exists($qrCodePath)) {
            $html .= '
        <div class="qr-code">
            <img src="' . $qrCodePath . '" alt="QR Code">
        </div>';
        }
        
        $html .= '
        <div class="footer">
            Issued: ' . date('F d, Y', strtotime($passData['pass_creation_date'])) . ' | GPMS
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
}
