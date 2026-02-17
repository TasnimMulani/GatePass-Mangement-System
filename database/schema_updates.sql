-- Database Schema Updates for Enhancements
-- Run this after importing the original getpassdb.sql

USE getpassdb;

-- Add new columns to passes table
ALTER TABLE passes 
ADD COLUMN qr_code_path VARCHAR(255) DEFAULT NULL AFTER status,
ADD COLUMN photo_path VARCHAR(255) DEFAULT NULL AFTER qr_code_path,
ADD COLUMN email_sent TINYINT(1) DEFAULT 0 AFTER photo_path;

-- Create visitor logs table for check-in/out tracking
CREATE TABLE IF NOT EXISTS visitor_logs (
  id INT(11) NOT NULL AUTO_INCREMENT,
  pass_id INT(11) NOT NULL,
  action ENUM('check_in', 'check_out') NOT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  notes TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (pass_id) REFERENCES passes(id) ON DELETE CASCADE,
  INDEX idx_pass_id (pass_id),
  INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add email configuration table (optional, for future SMTP settings UI)
CREATE TABLE IF NOT EXISTS email_config (
  id INT(11) NOT NULL AUTO_INCREMENT,
  smtp_host VARCHAR(255) NOT NULL,
  smtp_port INT(11) NOT NULL DEFAULT 587,
  smtp_username VARCHAR(255) NOT NULL,
  smtp_password VARCHAR(255) NOT NULL,
  from_email VARCHAR(255) NOT NULL,
  from_name VARCHAR(255) NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
