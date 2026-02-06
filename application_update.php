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
    
    if (isset($_POST['update'])) {
        $app_id = $_POST['application_id'];
        $interview_date = $_POST['interview_date'];
        $offer_salary = $_POST['offer_salary'];
        
        $job_query = $conn->query("SELECT j.salary FROM applications a 
                                   JOIN jobs j ON a.job_id = j.job_id 
                                   WHERE a.application_id = $app_id");
        if ($job_query->num_rows > 0) {
            $job_data = $job_query->fetch_assoc();
            $job_salary = $job_data['salary'];
            
            if (!empty($offer_salary) && !empty($job_salary)) {
                $offer_num = preg_replace('/[^0-9]/', '', $offer_salary);
                $job_num = preg_replace('/[^0-9]/', '', $job_salary);
                
                if ($offer_num > 0 && $job_num > 0 && $offer_num > $job_num * 2) {
                    $error_msg = "Warning: Offer salary seems too high compared to job salary range.";
                } else {
                    $sql = "UPDATE applications SET interview_date='$interview_date', offer_salary='$offer_salary' WHERE application_id=$app_id";
                    if ($conn->query($sql)) {
                        $success_msg = "Application updated successfully!";
                    } else {
                        $error_msg = "Error updating application.";
                    }
                }
            } else {
                $sql = "UPDATE applications SET interview_date='$interview_date', offer_salary='$offer_salary' WHERE application_id=$app_id";
                if ($conn->query($sql)) {
                    $success_msg = "Application updated successfully!";
                } else {
                    $error_msg = "Error updating application.";
                }
            }
        } else {
            $error_msg = "Error: Could not find job information for this application.";
        }
    }
    
    if (isset($_POST['delete'])) {
        $app_id = $_POST['application_id'];
        $sql = "DELETE FROM applications WHERE application_id=$app_id";
        if ($conn->query($sql)) {
            $success_msg = "Application deleted successfully!";
        } else {
            $error_msg = "Error deleting application.";
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
    <title>Application Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
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
        
        
        
        <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <h2>Application List</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 15px; text-align: left;">ID</th>
                        <th style="padding: 15px; text-align: left;">Job ID</th>
                        <th style="padding: 15px; text-align: left;">Job Salary</th>
                        <th style="padding: 15px; text-align: left;">Interview Date</th>
                        <th style="padding: 15px; text-align: left;">Offer Salary</th>
                        <th style="padding: 15px; text-align: left;">Applied Date</th>
                        <th style="padding: 15px; text-align: left;">Actions</th>
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
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $row['interview_date'] ?: '-'; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $row['offer_salary'] ?: '-'; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;"><?php echo $row['applied_date']; ?></td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                <div style="margin-bottom: 5px;">
                                    <input type="date" name="interview_date" value="<?php echo $row['interview_date']; ?>" placeholder="Interview Date" style="padding: 5px; width: 120px; margin-right: 5px;">
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <input type="text" name="offer_salary" value="<?php echo $row['offer_salary']; ?>" placeholder="Offer Salary" style="padding: 5px; width: 120px; margin-right: 5px;">
                                </div>
                                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">
                                    Max salary: <?php echo $row['job_salary'] ?: 'N/A'; ?>
                                </div>
                                <div>
                                    <button type="submit" name="update" style="background: #3498db; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;">Update</button>
                                    <button type="submit" name="delete" onclick="return confirmDelete();" style="background: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;">Delete</button>
                                </div>
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