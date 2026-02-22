# GatePass Management System

A comprehensive system for managing gate passes with AI-driven insights, QR code generation, and automated reporting.

## Features
- **Pass Management**: Create, view, and manage visitor passes.
- **QR Code Integration**: Scan and verify passes using QR codes.
- **AI-Driven Insights**: Intelligent traffic analysis and anomaly detection.
- **Reporting**: Generate PDF passes and email notifications.

## Prerequisites
- **Web Server**: XAMPP, WAMP, MAMP, or a standalone LAMP stack.
- **PHP**: Version 7.4 or higher recommended.
- **MySQL/MariaDB**: For database management.

## Installation and Setup

### 1. Database Configuration
1.  Open your MySQL management tool (e.g., phpMyAdmin).
2.  Create a new database named `getpassdb`.
3.  Import the SQL files from the `database` folder:
    -   First, import `database/getpassdb.sql`.
    -   Then, import `database/schema_updates.sql`.

### 2. Connect to Database
Open `db.php` in the root directory and ensure the database credentials match your local setup:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');   // Standard for XAMPP
define('DB_PASSWORD', '');       // Standard for XAMPP
define('DB_NAME', 'getpassdb');
```

### 3. Deploy the Application
1.  Copy the entire `getpass managment system` folder into your web server's root directory (e.g., `C:/xampp/htdocs/`).
2.  Ensure you have read/write permissions for the folder, especially for the `public` directory if assets are generated there.

### 4. Run the Project
1.  Start your Apache and MySQL servers.
2.  Open your browser and navigate to:
    `http://localhost/getpass managment system/index.php`

## Login Credentials
The default administrator credentials are:
- **Username**: `admin`
- **Password**: `admin`

(Note: The password is stored as an MD5 hash in the `admin` table).

## License
[Insert License Here]
