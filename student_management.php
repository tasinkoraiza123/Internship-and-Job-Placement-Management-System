<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $email = $_POST['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = "Invalid email format";
        } else {
            $admin_id = 1;
            $student_id = $conn->real_escape_string($_POST['student_id']);
            $f_name = $conn->real_escape_string($_POST['f_name']);
            $l_name = $conn->real_escape_string($_POST['l_name']);
            $email = $conn->real_escape_string($_POST['email']);
            $phone = $conn->real_escape_string($_POST['phone']);
            $password = $conn->real_escape_string($_POST['password']);
            $cgpa = $conn->real_escape_string($_POST['cgpa']);
            $department = $conn->real_escape_string($_POST['department']);
            
            $check_sql = "SELECT student_id FROM students WHERE student_id = '$student_id'";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error_msg = "Error: Student ID '$student_id' already exists!";
            } else {
                $sql = "INSERT INTO students (student_id, admin_id, f_name, l_name, email, phone, password, cgpa, department) 
                        VALUES ('$student_id', '$admin_id', '$f_name', '$l_name', '$email', '$phone', '$password', '$cgpa', '$department')";
                
                if ($conn->query($sql)) {
                    $success_msg = "Student added successfully!";
                } else {
                    $error_msg = "Error adding student: " . $conn->error;
                }
            }
        }
    }
    
    if (isset($_POST['delete'])) {
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $sql = "DELETE FROM students WHERE student_id = '$student_id'";
        
        if ($conn->query($sql)) {
            $success_msg = "Student deleted successfully!";
        } else {
            $error_msg = "Error deleting student: " . $conn->error;
        }
    }
}

$students = $conn->query("SELECT * FROM students ORDER BY student_id");
?>
<!DOCTYPE html>
<html>
<head>
    <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
    <title>Student Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .form input {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Management</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Student</h2>
            <form method="POST" class="form">
                <input type="text" name="student_id" placeholder="Student ID" required>
                <input type="text" name="f_name" placeholder="First Name" required>
                <input type="text" name="l_name" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone">
                <input type="password" name="password" placeholder="Password" required>
                <input type="number" step="0.01" name="cgpa" placeholder="CGPA" min="0" max="4">
                <input type="text" name="department" placeholder="Department">
                <button type="submit" name="add">Add Student</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Student List</h2>
            <?php if ($students && $students->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>CGPA</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['f_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['l_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['cgpa']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">No students found. Add your first student above.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>