<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$jobs = $conn->query("SELECT j.*, c.name as company_name FROM jobs j 
                      LEFT JOIN companies c ON j.company_id = c.company_id 
                      ORDER BY j.job_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Job List</title>
    <link rel="stylesheet" href="style.css">
    <style> body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; } 
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); } 
    h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; margin-bottom: 20px; } table { width: 100%; border-collapse: collapse; margin-top: 20px; } 
    th { background: #266355fa; color: white; padding: 12px 15px; text-align: left; } 
    td { padding: 12px 15px; border-bottom: 1px solid #ddd; } 
    tr:hover { background: #f9f9f9; } .applications { background: #ff9800; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; } 
    .actions a { text-decoration: none; padding: 5px 10px; margin-right: 5px; border-radius: 3px; font-size: 13px; } 
    .view-btn { background: #2196F3; color: white; } .edit-btn { background: #b4bdb4ff; color: white; } 
    .apps-btn { background: #FF9800; color: white; } .back-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #2196F3; } 
    .no-data { text-align: center; padding: 40px; color: #666; } 
    </style>
</head>
<body>
    <div class="container">
        
            <a href="index.php" class="dashboard-link">← Back to Dashboard</a> 
            <h1>Job List</h1>
        
        
        <?php if ($jobs && $jobs->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Min CGPA</th>
                        <th>Salary</th>
                        <th>Location</th>
                        <th>Description</th>
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
                        <td><?php echo $row['min_cgpa'] ? $row['min_cgpa'] : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($row['salary']) ?: 'N/A'; ?></td>
                        <td>
                            <?php 
                            $location = [];
                            if ($row['street_no']) $location[] = "St. #" . $row['street_no'];
                            if ($row['city']) $location[] = $row['city'];
                            echo $location ? implode(', ', $location) : 'N/A';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $desc = htmlspecialchars($row['description']);
                            if (strlen($desc) > 100) {
                                echo substr($desc, 0, 100) . '...';
                            } else {
                                echo $desc ?: 'No description';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-jobs">
                <p>No jobs available at the moment.</p>
                <p>Check back later for new job opportunities.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>