<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';
$filter_job = isset($_GET['job_id']) ? intval($_GET['job_id']) : null;

if ($filter_job) {
    $applications = $conn->query("SELECT a.*, j.salary AS job_salary 
                                  FROM applications a
                                  LEFT JOIN jobs j ON a.job_id = j.job_id
                                  WHERE a.job_id = $filter_job
                                  ORDER BY a.application_id");
} else {
    $applications = $conn->query("SELECT a.*, j.salary AS job_salary 
                                  FROM applications a
                                  LEFT JOIN jobs j ON a.job_id = j.job_id
                                  ORDER BY a.application_id");
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $job_id = $_POST['job_id'];
        
        $check_job = $conn->query("SELECT job_id FROM jobs WHERE job_id = $job_id");
        if ($check_job->num_rows > 0) {
            $sql = "INSERT INTO applications (job_id) VALUES ($job_id)";
            if ($conn->query($sql)) {
                $success_msg = "Application added successfully!";
            } else {
                $error_msg = "Error adding application.";
            }
        } else {
            $error_msg = "Error: Job ID $job_id does not exist. Please enter a valid Job ID.";
        }
    }
}

$applications = $conn->query("SELECT a.*, j.salary as job_salary 
                              FROM applications a 
                              LEFT JOIN jobs j ON a.job_id = j.job_id 
                              ORDER BY a.application_id");
?>
<!DOCTYPE html>
<html>
<head>
    <a href="student_dashboard.php" class="dashboard-link">← Back to Dashboard</a>
    <title>Application Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Application Management</h1>
        
        <?php if ($success_msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>
        
        <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <h2>Add New Application</h2>
            <form method="POST">
                <input type="number" name="job_id" placeholder="Job ID" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 15px;">
                <button type="submit" name="add" style="background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer;">Add Application</button>
            </form>
        </div>
        
        <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <h2>Application List</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 15px; text-align: left;">ID</th>
                        <th style="padding: 15px; text-align: left;">Job ID</th>
                        <th style="padding: 15px; text-align: left;">Job Salary</th>
                        <th style="padding: 15px; text-align: left;">Applied Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while($row = $applications->fetch_assoc()): 
                    ?>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $counter++; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $row['job_id']; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $row['job_salary'] ?: '-'; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $row['applied_date']; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this application?");
    }
    
    setTimeout(function() {
        var messages = document.querySelectorAll('div[style*="background: #d4edda"], div[style*="background: #f8d7da"]');
        messages.forEach(function(msg) {
            msg.style.display = 'none';
        });
    }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>