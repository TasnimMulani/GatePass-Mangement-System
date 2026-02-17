<?php
// AiInsights.php - AI-driven analytics and insights for gate pass management

class AiInsights {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Analyze traffic patterns and detect anomalies
     * @return array Array of insights with severity levels
     */
    public function getTrafficInsights() {
        $insights = [];
        
        // Check current hour traffic
        $currentHourTraffic = $this->getCurrentHourTraffic();
        $avgHourlyTraffic = $this->getAverageHourlyTraffic();
        
        if ($currentHourTraffic > ($avgHourlyTraffic * 2)) {
            $insights[] = [
                'type' => 'alert',
                'title' => 'High Traffic Alert',
                'message' => "Current hour has {$currentHourTraffic} passes, significantly higher than average ({$avgHourlyTraffic}).",
                'severity' => 'high'
            ];
        } elseif ($currentHourTraffic > ($avgHourlyTraffic * 1.5)) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Increased Traffic',
                'message' => "Traffic is {$currentHourTraffic} passes this hour, above average.",
                'severity' => 'medium'
            ];
        } else {
            $insights[] = [
                'type' => 'normal',
                'title' => 'Normal Traffic',
                'message' => "Traffic levels are normal with {$currentHourTraffic} passes this hour.",
                'severity' => 'low'
            ];
        }
        
        // Check for unusual hours
        $afterHoursCount = $this->getAfterHoursCount();
        if ($afterHoursCount > 0) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'After-Hours Activity',
                'message' => "{$afterHoursCount} passes issued after 8 PM in the last 7 days.",
                'severity' => 'medium'
            ];
        }
        
        // Check for repeat visitors
        $repeatVisitors = $this->getRepeatVisitors();
        if (count($repeatVisitors) > 0) {
            $insights[] = [
                'type' => 'normal',
                'title' => 'Frequent Visitors',
                'message' => count($repeatVisitors) . " visitors have visited multiple times this week.",
                'severity' => 'low'
            ];
        }
        
        return $insights;
    }
    
    /**
     * Get traffic count for current hour
     */
    private function getCurrentHourTraffic() {
        $query = "SELECT COUNT(*) as count FROM passes 
                  WHERE HOUR(pass_creation_date) = HOUR(NOW()) 
                  AND DATE(pass_creation_date) = CURDATE()";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)$row['count'];
    }
    
    /**
     * Get average hourly traffic over the last 7 days
     */
    private function getAverageHourlyTraffic() {
        $query = "SELECT COUNT(*) / (7 * 24) as avg_hourly FROM passes 
                  WHERE pass_creation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        return max(1, round($row['avg_hourly'])); // Minimum 1 to avoid division by zero
    }
    
    /**
     * Get count of passes issued after 8 PM
     */
    private function getAfterHoursCount() {
        $query = "SELECT COUNT(*) as count FROM passes 
                  WHERE HOUR(pass_creation_date) >= 20 
                  AND pass_creation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)$row['count'];
    }
    
    /**
     * Get list of repeat visitors (visited more than once this week)
     */
    private function getRepeatVisitors() {
        $query = "SELECT full_name, contact_number, COUNT(*) as visit_count 
                  FROM passes 
                  WHERE pass_creation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY contact_number 
                  HAVING visit_count > 1 
                  ORDER BY visit_count DESC 
                  LIMIT 5";
        $result = mysqli_query($this->conn, $query);
        $visitors = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $visitors[] = $row;
        }
        return $visitors;
    }
    
    /**
     * Get peak hours for the last 7 days
     */
    public function getPeakHours() {
        $query = "SELECT HOUR(pass_creation_date) as hour, COUNT(*) as count 
                  FROM passes 
                  WHERE pass_creation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY HOUR(pass_creation_date) 
                  ORDER BY count DESC 
                  LIMIT 3";
        $result = mysqli_query($this->conn, $query);
        $peakHours = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $peakHours[] = [
                'hour' => $row['hour'],
                'count' => $row['count']
            ];
        }
        return $peakHours;
    }
    
    /**
     * Get visitor category distribution
     */
    public function getCategoryDistribution() {
        $query = "SELECT category, COUNT(*) as count 
                  FROM passes 
                  WHERE pass_creation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY category 
                  ORDER BY count DESC";
        $result = mysqli_query($this->conn, $query);
        $distribution = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $distribution[] = $row;
        }
        return $distribution;
    }
    
    /**
     * Predict busy days based on historical data
     */
    public function predictBusyDays() {
        $query = "SELECT DAYNAME(pass_creation_date) as day_name, COUNT(*) as count 
                  FROM passes 
                  WHERE pass_creation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY DAYOFWEEK(pass_creation_date), DAYNAME(pass_creation_date)
                  ORDER BY count DESC";
        $result = mysqli_query($this->conn, $query);
        $days = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $days[] = $row;
        }
        return $days;
    }
}
