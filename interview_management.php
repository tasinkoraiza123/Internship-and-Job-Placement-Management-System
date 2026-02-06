<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $application_id = (int)$_POST['application_id'];
        $interviewer_name = $conn->real_escape_string($_POST['interviewer_name']);
        $scheduled_date = $conn->real_escape_string($_POST['scheduled_date']);
        
        $sql = "INSERT INTO interviews (application_id, interviewer_name, scheduled_date) 
                VALUES ($application_id, '$interviewer_name', '$scheduled_date')";
        
        if ($conn->query($sql)) {
            $success_msg = "Interview scheduled successfully!";
        } else {
            $error_msg = "Error scheduling interview.";
        }
    }
    
    if (isset($_POST['delete'])) {
        $interview_id = (int)$_POST['interview_id'];
        $sql = "DELETE FROM interviews WHERE interview_id = $interview_id";
        
        if ($conn->query($sql)) {
            $success_msg = "Interview deleted successfully!";
        } else {
            $error_msg = "Error deleting interview.";
        }
    }
}

$interviews = $conn->query("
    SELECT i.*, a.application_id, a.job_id 
    FROM interviews i 
    LEFT JOIN applications a ON i.application_id = a.application_id 
    ORDER BY i.scheduled_date 
");

$applications = $conn->query("SELECT application_id, job_id FROM applications");
?>
<!DOCTYPE html>
<html>
<head>
    <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
    <title>Interview Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Interview Management</h1>
        
        <?php if ($success_msg): ?>
        <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
        <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Schedule New Interview</h2>
            <form method="POST">
                <select name="application_id" required>
                    <option value="">Select Application</option>
                    <?php while($app = $applications->fetch_assoc()): ?>
                        <option value="<?php echo $app['application_id']; ?>">
                            Application #<?php echo $app['application_id']; ?> (Job: <?php echo $app['job_id']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="interviewer_name" placeholder="Interviewer Name" required>
                <input type="date" name="scheduled_date" required>
                <button type="submit" name="add">Schedule Interview</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Interview Dates List</h2>
            <?php if ($interviews->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Application ID</th>
                        <th>Job ID</th>
                        <th>Interviewer</th>
                        <th>Scheduled Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while($row = $interviews->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo $row['application_id']; ?></td>
                        <td><?php echo $row['job_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['interviewer_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['scheduled_date'])); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="interview_id" value="<?php echo $row['interview_id']; ?>">
                                <button type="submit" name="delete" class="btn-danger" 
                                        onclick="return confirm('Delete this interview?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No interviews scheduled.</p>
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