<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$error_msg = '';
$success_msg = '';

$offered_applications = $conn->query("
    SELECT a.application_id, a.offer_salary, j.title as job_title, j.job_id,c.name as company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN companies c ON j.company_id = c.company_id
    WHERE a.offer_salary IS NOT NULL
    AND a.offer_salary != ''
    AND a.application_id NOT IN (
        SELECT DISTINCT pr.application_id 
        FROM placement_records pr 
        WHERE pr.application_id IS NOT NULL
    )
    ORDER BY a.application_id DESC
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['appoint_from_application'])) {
        $application_id = (int)$_POST['application_id'];
        
        $app_query = $conn->query("
            SELECT a.*, j.title as job_title, j.company_id, 
                   c.name as company_name
            FROM applications a
            JOIN jobs j ON a.job_id = j.job_id
            JOIN companies c ON j.company_id = c.company_id
            WHERE a.application_id = $application_id
        ");
        
        if ($app_query->num_rows > 0) {
            $app = $app_query->fetch_assoc();
            
            $company_id = $app['company_id'];
            $job_title = $conn->real_escape_string($app['job_title']);
            $join_date = date('Y-m-d'); 
            $salary_package = $conn->real_escape_string($app['offer_salary']);
            
            $check_placed = $conn->query("
                SELECT placement_id FROM placement_records 
                WHERE application_id = $application_id
            ");
            
            if ($check_placed->num_rows == 0) {
                $sql = "INSERT INTO placement_records (company_id, job_title, join_date, salary_package, application_id) 
                        VALUES ($company_id, '$job_title', '$join_date', '$salary_package', $application_id)";
                
                if ($conn->query($sql)) {
                    $success_msg = "Student appointed successfully from application!";
                } else {
                    $error_msg = "Error: " . $conn->error;
                }
            } else {
                $error_msg = "This application is already placed!";
            }
        } else {
            $error_msg = "Application not found!";
        }
    }
}
if (isset($_POST['delete_application'])) {
        $application_id = (int)$_POST['application_id'];
        
        $check_app = $conn->query("SELECT * FROM applications WHERE application_id = $application_id");
        
        if ($check_app->num_rows > 0) {
            $check_placed = $conn->query("
                SELECT placement_id FROM placement_records 
                WHERE application_id = $application_id
            ");
            
            if ($check_placed->num_rows == 0) {
                $delete_sql = "DELETE FROM applications WHERE application_id = $application_id";
                
                if ($conn->query($delete_sql)) {
                    $success_msg = "Application deleted successfully!";
                    $offered_applications = $conn->query("
                        SELECT a.application_id, a.offer_salary, j.title as job_title, j.job_id,c.name as company_name
                        FROM applications a
                        JOIN jobs j ON a.job_id = j.job_id
                        JOIN companies c ON j.company_id = c.company_id
                        WHERE a.offer_salary IS NOT NULL
                        AND a.offer_salary != ''
                        AND a.application_id NOT IN (
                            SELECT DISTINCT pr.application_id 
                            FROM placement_records pr 
                            WHERE pr.application_id IS NOT NULL
                        )
                        ORDER BY a.application_id DESC
                    ");
                } else {
                    $error_msg = "Error deleting application: " . $conn->error;
                }
            } else {
                $error_msg = "Cannot delete: This application is already appointed!";
            }
        } else {
            $error_msg = "Application not found!";
        }
    }

$placements = $conn->query("
    SELECT pr.*, c.name as company_name
    FROM placement_records pr 
    LEFT JOIN companies c ON pr.company_id = c.company_id 
    ORDER BY pr.placement_id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Placement Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
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
        
        .btn-success {
            background: #109819ff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-success1 {
            background: #981010ff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .appointment-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background: #3498db;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .status-appointed {
            color: green;
            font-weight: bold;
        }
        
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        
        .status-no-offer {
            color: #999;
        }
        
        h2 {
            color: #444;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
        <h1>Placement Management</h1>
        
        <?php if ($success_msg): ?>
        <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
        <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="appointment-section">
            <h2>Appoint from Applications</h2>
            
            <?php if ($offered_applications && $offered_applications->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>App ID</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Offer Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while($app = $offered_applications->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['offer_salary']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                    <button type="submit" name="appoint_from_application" class="btn-success" 
                                            onclick="return confirm('Appoint student for this application?')">
                                        Appoint
                                    </button>
                                    <button type="submit" name="appoint_from_application" class="btn-success1" 
                                            onclick="return confirm('Delete student for this application?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">
                    No applications with offers available for appointment.
                </p>
            <?php endif; ?>
        </div>
        
        <div class="appointment-section" style="margin-top: 30px;">
            <h2>All Applications</h2>
            
            <?php 
            $all_applications = $conn->query("
                SELECT a.application_id, a.offer_salary, a.applied_date,
                       j.title as job_title, j.job_id,
                       c.name as company_name,
                       CASE 
                           WHEN pr.placement_id IS NOT NULL THEN 'Yes'
                           ELSE 'No'
                       END as is_appointed
                FROM applications a
                JOIN jobs j ON a.job_id = j.job_id
                JOIN companies c ON j.company_id = c.company_id
                LEFT JOIN placement_records pr ON a.application_id = pr.application_id
                ORDER BY a.application_id DESC
            ");
            
            if ($all_applications && $all_applications->num_rows > 0): 
            ?>
                <table>
                    <thead>
                        <tr>
                            <th>App ID</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Offer Salary</th>
                            <th>Applied Date</th>
                            <th>Appointed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while($app = $all_applications->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $app['application_id']; ?></td>
                            <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['offer_salary'] ?: 'No Offer'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($app['applied_date'])); ?></td>
                            <td>
                                <span style="color: <?php echo $app['is_appointed'] == 'Yes' ? 'green' : 'red'; ?>; 
                                      font-weight: bold;">
                                    <?php echo $app['is_appointed']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['offer_salary'] && $app['offer_salary'] != 'No Offer'): ?>
                                    <?php if ($app['is_appointed'] == 'Yes'): ?>
                                        <span class="status-appointed">Appointed</span>
                                    <?php else: ?>
                                        <span class="status-pending">Rejected</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-no-offer">No Offer</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">
                    No applications found.
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        setTimeout(function() {
            var messages = document.querySelectorAll('.alert');
            messages.forEach(function(msg) {
                msg.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>