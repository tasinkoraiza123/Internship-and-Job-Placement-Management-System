<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'student':
            header("Location: student_dashboard.php");
            break;
        case 'company':
            header("Location: company_dashboard.php");
            break;
        case 'admin':
            header("Location: admin_dashboard.php");
            break;
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required!";
    } else {
        $email = $conn->real_escape_string($email);
        
        if ($role == 'student') {
            $sql = "SELECT student_id, password, f_name FROM students WHERE email='$email'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if ($password == $user['password']) {
                    $_SESSION['user_id'] = $user['student_id'];
                    $_SESSION['role'] = 'student';
                    $_SESSION['name'] = $user['f_name'];
                    header("Location: student_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Student not found!";
            }
        } 
        elseif ($role == 'company') {
            $sql = "SELECT company_id, password, name FROM companies WHERE email='$email'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($password == $user['password']) {
                    $_SESSION['user_id'] = $user['company_id'];
                    $_SESSION['role'] = 'company';
                    $_SESSION['name'] = $user['name'];
                    header("Location: company_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Company not found!";
            }
        }
        elseif ($role == 'admin') {
            
            $sql = "SELECT admin_id, password, adminname FROM admin WHERE adminname='$email' OR email='$email'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($password == $user['password']) {
                    $_SESSION['user_id'] = $user['admin_id'];
                    $_SESSION['role'] = 'admin';
                    $_SESSION['name'] = $user['adminname'];
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Admin not found!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #5a51db7b;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .login-container {
            background-color: #ffffff;
            padding: 40px;
            width: 350px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #000000;
            text-align: center;
            margin-bottom: 40px;
            font-size: 28px;
            font-weight: normal;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333333;
            font-size: 14px;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            background-color: #ffffff;
            color: #333333;
            box-sizing: border-box;
            font-size: 14px;
        }
        
        input::placeholder {
            color: #888888;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.2s;
        }
        
        .login-btn:hover {
            background-color: #0056b3;
        }
        
        .register-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .register-text {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .register-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .register-btn:hover {
            background-color: #218838;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
        }
        
        .note {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            color: #666;
            font-size: 13px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Select Role</label>
                <select name="role" required>
                    <option value="">Choose Role</option>
                    <option value="student">Student</option>
                    <option value="company">Company</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email or Username:</label>
                <input type="text" name="email" placeholder="Enter your email or username" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <div class="register-section">
        <div class="register-text"> Don't have an account? 
        <a href="student_register.php" class="inline-link">Register as Student</a> or 
        <a href="company_register.php" class="inline-link">Register as Company</a>
        </div>
       </div>
    </div>
</body>
</html>