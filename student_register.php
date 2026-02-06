<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $student_id = trim($_POST['student_id']);
    $admin_id = 1; 
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $f_name = trim($_POST['f_name']);
    $l_name = trim($_POST['l_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $cgpa = $_POST['cgpa'];
    $department = trim($_POST['department']);
    
    
    if (empty($student_id) || empty($password) || empty($f_name) || empty($email)) {
        $error = "Required fields are missing!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif ($cgpa && ($cgpa < 0 || $cgpa > 4.00)) {
        $error = "CGPA must be between 0 and 4.00";
    } else {
    
        $check_id = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $check_id->bind_param("s", $student_id);
        $check_id->execute();
        $check_id->store_result();
        
        if ($check_id->num_rows > 0) {
            $error = "Student ID already exists!";
        } else {
           
            $check_email = $conn->prepare("SELECT email FROM students WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                $error = "Email already registered!";
            } else {
                
                $sql = "INSERT INTO students (student_id, admin_id, password, f_name, l_name, email, phone, cgpa, department) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sisssssds", 
                    $student_id, 
                    $admin_id, 
                    $password, 
                    $f_name, 
                    $l_name, 
                    $email, 
                    $phone, 
                    $cgpa, 
                    $department
                );
                
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now login.";
                    $_POST = array();
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
                
                $stmt->close();
            }
            $check_email->close();
        }
        $check_id->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #7b6dc994;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .registration-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        .required {
            color: red;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .row {
            display: flex;
            gap: 15px;
        }
        
        .row .form-group {
            flex: 1;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background-color: #218838;
        }
        
        .back-btn {
            width: 100%;
            padding: 12px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 10px;
            cursor: pointer;
        }
        
        .back-btn:hover {
            background-color: #5a6268;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .note {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h1>Student Registration</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="form-group">
                    <label>Student ID <span class="required"></span></label>
                    <input type="text" name="student_id" 
                           value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>"
                           placeholder="e.g., 12345" required>
                </div>
                
                <div class="form-group">
                    <label>CGPA</label>
                    <input type="number" name="cgpa" step="0.01" min="0" max="4.00"
                           value="<?php echo isset($_POST['cgpa']) ? htmlspecialchars($_POST['cgpa']) : ''; ?>"
                           placeholder="e.g., 3.75">
                </div>
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label>First Name <span class="required"></span></label>
                    <input type="text" name="f_name" 
                           value="<?php echo isset($_POST['f_name']) ? htmlspecialchars($_POST['f_name']) : ''; ?>"
                           placeholder="Enter first name" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="l_name" 
                           value="<?php echo isset($_POST['l_name']) ? htmlspecialchars($_POST['l_name']) : ''; ?>"
                           placeholder="Enter last name">
                </div>
            </div>
            
            <div class="form-group">
                <label>Email <span class="required"></span></label>
                <input type="email" name="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Enter email address" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                       placeholder="Enter phone number">
            </div>
            
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" 
                       value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>"
                       placeholder="e.g., Computer Science">
            </div>
            
            <div class="row">
                <div class="form-group">
                    <label>Password <span class="required"></span></label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password <span class="required"></span></label>
                    <input type="password" name="confirm_password" placeholder="Confirm password" required>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Register</button>
            <a href="login.php" class="back-btn">Back to Login</a>
        </form>
    </div>
</body>
</html>