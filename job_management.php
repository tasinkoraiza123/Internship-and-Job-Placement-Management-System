<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $min_cgpa = $_POST['min_cgpa'];
        $salary = $conn->real_escape_string($_POST['salary']);
        $street_no = (int)$_POST['street_no'];
        $city = $conn->real_escape_string($_POST['city']);
        $company_id = (int)$_POST['company_id'];
        
        $sql = "INSERT INTO jobs (title, description, min_cgpa, salary, street_no, city, company_id) 
                VALUES ('$title', '$description', '$min_cgpa', '$salary', '$street_no', '$city', '$company_id')";
        
        if ($conn->query($sql)) {
            $success_msg = "Job posted successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete'])) {
        $job_id = (int)$_POST['job_id'];
        $sql = "DELETE FROM jobs WHERE job_id = $job_id";
        
        if ($conn->query($sql)) {
            $success_msg = "Job deleted successfully!";
        } else {
            $error_msg = "Error deleting job: " . $conn->error;
        }
    }
}

$jobs = $conn->query("SELECT j.*, c.name as company_name FROM jobs j 
                      LEFT JOIN companies c ON j.company_id = c.company_id 
                      ORDER BY j.job_id DESC");

$companies = $conn->query("SELECT * FROM companies ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Job Management</title>
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
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
            margin-bottom: 15px;
        }
        
        .form input, .form select, .form textarea {
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
        <h1>Job Management</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Job</h2>
            <form method="POST" class="form">
                <input type="text" name="title" placeholder="Job Title" required>
                <textarea name="description" placeholder="Job Description"></textarea>
                <input type="number" step="0.01" name="min_cgpa" placeholder="Minimum CGPA" min="0" max="4">
                <input type="text" name="salary" placeholder="Salary">
                <input type="number" name="street_no" placeholder="Street Number">
                <input type="text" name="city" placeholder="City">
                <select name="company_id" required>
                    <option value="">Select Company</option>
                    <?php while($c = $companies->fetch_assoc()): ?>
                        <option value="<?php echo $c['company_id']; ?>">
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add">Post Job</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Job List</h2>
            <?php if ($jobs && $jobs->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Min CGPA</th>
                        <th>Salary</th>
                        <th>Street No</th>
                        <th>City</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while($row = $jobs->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td><?php echo $row['min_cgpa']; ?></td>
                        <td><?php echo htmlspecialchars($row['salary']); ?></td>
                        <td><?php echo $row['street_no']; ?></td>
                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="job_id" value="<?php echo $row['job_id']; ?>">
                                <button type="submit" name="delete" class="btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this job?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">No jobs found. Add your first job above.</p>
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