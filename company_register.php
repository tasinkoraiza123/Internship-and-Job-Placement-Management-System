<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $page = $_SESSION['role'] . '_dashboard.php';
    header("Location: $page");
    exit();
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $desc = trim($_POST['description']);
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Required fields missing!";
    } elseif ($password !== $confirm) {
        $error = "Passwords don't match!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email!";
    } else {
        $stmt = $conn->prepare("SELECT company_id FROM companies WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO companies (name, email, password, phone, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $password, $phone, $desc);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
                $_POST = [];
            } else {
                $error = "Registration failed!";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Company Registration</title>
    <style>
        body { font-family: Arial; background: linear-gradient(135deg, #a1b6f596 0%, #764ba2 100%); 
               min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { width: 100%; max-width: 500px; background: white; border-radius: 15px; 
                     box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #d6daddff 0%, #d5d8dcff 100%); color: black; 
                  padding: 25px; text-align: center; }
        .back-btn { position: absolute; top: 20px; left: 20px; background: rgba(255,255,255,0.1); 
                    color: white; padding: 8px 15px; text-decoration: none; border-radius: 6px; }
        .form-container { padding: 30px; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-group { margin-bottom: 20px; }
        input, textarea { width: 95%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; 
                         margin-top: 5px; }
        .password-field { position: relative; }
        .password-toggle { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); 
                           background: none; border: none; cursor: pointer; }
        .form-row { display: flex; gap: 15px; }
        .submit-btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%); 
                     color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        @media (max-width: 600px) { .form-row { flex-direction: column; } }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="header">
            <h1>Company Registration</h1>
        </div>
        
        <div class="form-container">
            <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Company Name </label>
                    <input type="text" name="name" value="<?= $_POST['name'] ?? '' ?>" placeholder="Company name" required>
                </div>
                
                <div class="form-group">
                    <label>Email </label>
                    <input type="email" name="email" value="<?= $_POST['email'] ?? '' ?>" placeholder="Email" required>
                    <small style="color:#666">Email must be unique</small>
                </div>
                
                    <div class="form-group">
                        <label>Password </label>
                        <div class="password-field">
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm </label>
                        <div class="password-field">
                            <input type="password" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                    </div>
                
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= $_POST['phone'] ?? '' ?>" placeholder="Phone">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Company description"><?= $_POST['description'] ?? '' ?></textarea>
                </div>
                <button type="submit" style="width:100%; padding:15px; background:#28a745; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Register</button>
                <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="background:white; color:#333; padding:8px 15px; border-radius:6px; text-decoration:none; border:1px solid #ddd; display: inline-block;">
                  Back to Login </a>
                </div>
            </div>
            </form>
        </div>
    </div>
</body>
</html>