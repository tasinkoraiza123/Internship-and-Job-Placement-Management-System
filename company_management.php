<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $conn->real_escape_string($_POST['password']);
    $description = $conn->real_escape_string($_POST['description']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format! Please enter a valid email.";
    } else {
        $check_sql = "SELECT company_id FROM companies WHERE email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error_msg = "Error: Email '$email' is already registered to another company!";
        } else {
            $sql = "INSERT INTO companies (name, email, phone, password, description) 
                    VALUES ('$name', '$email', '$phone', '$password', '$description')";
            
            if ($conn->query($sql)) {
                $success_msg = "Company added successfully!";
            } else {
                $error_msg = "Error adding company: " . $conn->error;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $company_id = (int)$_POST['company_id'];
    
    $sql = "DELETE FROM companies WHERE company_id = $company_id";
    
    if ($conn->query($sql)) {
        $success_msg = "Company deleted successfully!";
    } else {
        $error_msg = "Error deleting company: " . $conn->error;
    }
}

$sql = "SELECT * FROM companies ORDER BY company_id";
$result = $conn->query($sql);

if (!$result) {
    $result = $conn->query("SELECT * FROM companies");
}
?>
<!DOCTYPE html>
<html>
<head>
    <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
    <title>Company Management</title>
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
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }
        
        .form input, .form textarea {
            margin-bottom: 15px;
        }
        
        .alert.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Company Management</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Company</h2>
            <form method="POST" class="form">
                <input type="text" name="name" placeholder="Company Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <small style="color: #666; display: block; margin-bottom: 5px;">Email must be unique for each company</small>
                <input type="text" name="phone" placeholder="Phone">
                <input type="text" name="password" placeholder="Password">
                <textarea name="description" placeholder="Company Description"></textarea>
                <button type="submit" name="add">Add Company</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Company List</h2>
            <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td>
                            <?php 
                            $desc = $row['description'] ?? '';
                            echo htmlspecialchars(substr($desc, 0, 100));
                            if (strlen($desc) > 100) {
                                echo '....';
                            }
                            ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="company_id" value="<?php echo $row['company_id']; ?>">
                                <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete this company?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">No companies found. Add your first company above.</p>
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
<?php
$conn->close();
?>