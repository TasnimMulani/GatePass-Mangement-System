<?php
// index.php - Modern Login Page
session_start();
include('app/config/db.php');
$error = '';

// If user is already logged in, redirect to dashboard
if(isset($_SESSION['admin_id'])){
    header("location: dashboard.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Using md5 for simplicity

    $sql = "SELECT id FROM admin WHERE username = '$username' and password = '$password'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    
    $count = mysqli_num_rows($result);
      
    if($count == 1) {
        $_SESSION['admin_id'] = $row['id'];
        header("location: dashboard.php");
    } else {
        $error = "Your Login Name or Password is invalid";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GPMS AI-Driven System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card glass">
            <div class="text-center">
                <h2>GPMS</h2>
                <p>AI-Driven Gate Pass Management System</p>
            </div>
            <h5 class="mb-4">Admin Login</h5>
            <form action="" method="post">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                </div>

                <?php if($error != ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
    <script src="public/js/script.js"></script>
</body>
</html>
