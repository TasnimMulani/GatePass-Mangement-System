<?php
// EmailNotification.php - Email notification handler using PHPMailer

class EmailNotification {
    private $mailer;
    private $config;
    
    public function __construct() {
        // Email configuration (you can move this to config file)
        $this->config = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => 'your-email@gmail.com', // UPDATE THIS
            'smtp_password' => 'your-app-password',     // UPDATE THIS (use App Password for Gmail)
            'from_email' => 'noreply@gpms.com',
            'from_name' => 'GPMS - Gate Pass Management System'
        ];
    }
    
    /**
     * Send pass creation notification
     * @param array $passData - Pass details
     * @param string $recipientEmail - Recipient email address
     * @return bool - Success status
     */
    public function sendPassCreatedEmail($passData, $recipientEmail) {
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        $subject = "Gate Pass Created - Pass #" . $passData['pass_number'];
        
        $body = $this->getPassEmailTemplate($passData);
        
        return $this->sendEmail($recipientEmail, $passData['full_name'], $subject, $body);
    }
    
    /**
     * Send email using simple PHP mail() function
     * For production, integrate PHPMailer library for SMTP
     */
    private function sendEmail($toEmail, $toName, $subject, $htmlBody) {
        // Simple headers for HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->config['from_name'] . " <" . $this->config['from_email'] . ">" . "\r\n";
        
        // Send email
        $success = @mail($toEmail, $subject, $htmlBody, $headers);
        
        return $success;
    }
    
    /**
     * Get HTML email template for pass creation
     */
    private function getPassEmailTemplate($passData) {
        $html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; margin: -30px -30px 20px -30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .pass-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
        .detail-row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #666; }
        .value { color: #333; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0; color: #666; font-size: 12px; }
        .qr-section { text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>🎫 Gate Pass Created Successfully</h1>
            </div>
            
            <p>Dear ' . htmlspecialchars($passData['full_name']) . ',</p>
            <p>Your gate pass has been successfully created. Please find the details below:</p>
            
            <div class="pass-details">
                <div class="detail-row">
                    <span class="label">Pass Number:</span>
                    <span class="value">' . htmlspecialchars($passData['pass_number']) . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Category:</span>
                    <span class="value">' . htmlspecialchars($passData['category']) . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Valid From:</span>
                    <span class="value">' . date('F d, Y', strtotime($passData['from_date'])) . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">Valid To:</span>
                    <span class="value">' . date('F d, Y', strtotime($passData['to_date'])) . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">ID Type:</span>
                    <span class="value">' . htmlspecialchars($passData['identity_type']) . '</span>
                </div>
                <div class="detail-row">
                    <span class="label">ID Number:</span>
                    <span class="value">' . htmlspecialchars($passData['identity_card_no']) . '</span>
                </div>
            </div>
            
            <p><strong>Note:</strong> Please present this pass at the entrance. You can also show the QR code for quick check-in.</p>
            
            <div class="footer">
                <p>This is an automated email from GPMS. Please do not reply.</p>
                <p>&copy; 2026 Gate Pass Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Send pass expiry reminder
     */
    public function sendExpiryReminder($passData, $recipientEmail) {
        if (empty($recipientEmail)) {
            return false;
        }
        
        $subject = "Gate Pass Expiring Soon - Pass #" . $passData['pass_number'];
        
        $body = "<p>Dear " . $passData['full_name'] . ",</p>";
        $body .= "<p>Your gate pass (Pass #" . $passData['pass_number'] . ") is expiring on " . date('F d, Y', strtotime($passData['to_date'])) . ".</p>";
        $body .= "<p>If you need to extend your pass, please contact the administration.</p>";
        
        return $this->sendEmail($recipientEmail, $passData['full_name'], $subject, $body);
    }
}
