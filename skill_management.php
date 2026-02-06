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
        $skill_name = $conn->real_escape_string($_POST['skill_name']);
        $description = $conn->real_escape_string($_POST['description']);

        $check_sql = "SELECT skill_id FROM skills WHERE skill_name = '$skill_name'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error_msg = "Error: Skill '$skill_name' already exists!";
        } else {
            $sql = "INSERT INTO skills (skill_name, description) VALUES ('$skill_name', '$description')";
            
            if ($conn->query($sql)) {
                $success_msg = "Skill added successfully!";
            } else {
                $error_msg = "Error adding skill: " . $conn->error;
            }
        }
    }
    
    if (isset($_POST['delete'])) {
        $skill_id = (int)$_POST['skill_id'];
        $sql = "DELETE FROM skills WHERE skill_id = $skill_id";
        
        if ($conn->query($sql)) {
            $success_msg = "Skill deleted successfully!";
        } else {
            $error_msg = "Error deleting skill.";
        }
    }
}

$skills = $conn->query("SELECT * FROM skills ORDER BY skill_name");
?>
<!DOCTYPE html>
<html>
<head>
    <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
    <title>Skill Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Skill Management</h1>
        
        <?php if ($success_msg): ?>
        <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
        <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Skill</h2>
            <form method="POST">
                <input type="text" name="skill_name" placeholder="Skill Name" required>
                <small style="color: #666; display: block; margin-bottom: 5px;">Skill names must be unique</small>
                <textarea name="description" placeholder="Skill Description" rows="3"></textarea>
                <button type="submit" name="add">Add Skill</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Skill List</h2>
            <?php if ($skills->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Skill Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while($row = $skills->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($row['skill_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="skill_id" value="<?php echo $row['skill_id']; ?>">
                                <button type="submit" name="delete" class="btn-danger" 
                                        onclick="return confirm('Delete this skill?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No skills found.</p>
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