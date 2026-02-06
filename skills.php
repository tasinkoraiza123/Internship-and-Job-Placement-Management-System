<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_profile'])) {
    $skill_id = (int)$_POST['skill_id'];
    $proficiency = $conn->real_escape_string($_POST['proficiency']);
    
    $skill_check = $conn->query("SELECT * FROM skills WHERE skill_id = $skill_id");
    if ($skill_check->num_rows == 0) {
        $error_msg = "Skill not found!";
    } else {
        $check_sql = "SELECT * FROM belong_to WHERE student_id = '$user_id' AND skill_id = $skill_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error_msg = "You already have this skill in your profile!";
        } else {
            $sql = "INSERT INTO belong_to (student_id, skill_id, proficiency_level) 
                    VALUES ('$user_id', $skill_id, '$proficiency')";
            
            if ($conn->query($sql)) {
                $success_msg = "Skill added to your profile successfully!";
            } else {
                $error_msg = "Error adding skill: " . $conn->error;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_profile'])) {
    $skill_id = (int)$_POST['skill_id'];
    $sql = "DELETE FROM belong_to WHERE student_id = '$user_id' AND skill_id = $skill_id";
    
    if ($conn->query($sql)) {
        $success_msg = "Skill removed from your profile!";
    } else {
        $error_msg = "Error removing skill: " . $conn->error;
    }
}

$skills = $conn->query("SELECT * FROM skills ORDER BY skill_name");

$student_skills = $conn->query("
    SELECT s.skill_id, s.skill_name, s.description, b.proficiency_level
    FROM skills s
    JOIN belong_to b ON s.skill_id = b.skill_id
    WHERE b.student_id = '$user_id'
    ORDER BY s.skill_name
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Skills - Student Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
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
        
        h2 {
            color: #444;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background: #3498db;
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        
        .btn-add {
            background: #27ae60;
            color: white;
        }
        
        .btn-remove {
            background: #e74c3c;
            color: white;
        }
        
        select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 5px;
        }
        
        .proficiency-badge {
            padding: 4px 10px;
            font-size: 12px;
            display: inline-block;
        }
        
        .beginner {
            background: #ffc107;
            color: #212529;
        }
        
        .intermediate {
            background: #fd7e14;
            color: white;
        }
        
        .advanced {
            background: #28a745;
            color: white;
        }
        
        .no-skills {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        
        .skill-info {
            margin-bottom: 8px;
        }
        
        .skill-name {
            font-weight: bold;
            color: #333;
        }
        
        .skill-desc {
            color: #666;
            font-size: 13px;
            margin-top: 3px;
        }
        
        .already-added {
            color: #28a745;
            font-weight: bold;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="student_dashboard.php" class="dashboard-link">← Back to Dashboard</a>
        <h1>Skills Management</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h2>My Skills Profile</h2>
            
            <?php if ($student_skills && $student_skills->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Skill Name</th>
                            <th>Description</th>
                            <th>Proficiency Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($skill = $student_skills->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="skill-name"><?php echo htmlspecialchars($skill['skill_name']); ?></div>
                            </td>
                            <td>
                                <div class="skill-desc"><?php echo htmlspecialchars($skill['description']); ?></div>
                            </td>
                            <td>
                                <span class="proficiency-badge <?php echo $skill['proficiency_level']; ?>">
                                    <?php echo ucfirst($skill['proficiency_level']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="skill_id" value="<?php echo $skill['skill_id']; ?>">
                                    <button type="submit" name="remove_from_profile" class="btn btn-remove" 
                                            onclick="return confirm('Remove this skill from your profile?')">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-skills">
                    <p>You haven't added any skills to your profile yet.</p>
                    <p>Browse available skills below and add them to your profile.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Available Skills</h2>
            
            <?php if ($skills && $skills->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Skill Name</th>
                            <th>Description</th>
                            <th>Add to Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($skill = $skills->fetch_assoc()): 
                            $check = $conn->query("SELECT * FROM belong_to WHERE student_id = '$user_id' AND skill_id = {$skill['skill_id']}");
                            $has_skill = $check->num_rows > 0;
                        ?>
                        <tr>
                            <td>
                                <div class="skill-name"><?php echo htmlspecialchars($skill['skill_name']); ?></div>
                            </td>
                            <td>
                                <div class="skill-desc"><?php echo htmlspecialchars($skill['description']); ?></div>
                            </td>
                            <td>
                                <?php if (!$has_skill): ?>
                                    <form method="POST">
                                        <input type="hidden" name="skill_id" value="<?php echo $skill['skill_id']; ?>">
                                        <select name="proficiency" required>
                                            <option value="">Select Level</option>
                                            <option value="beginner">Beginner</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                        <button type="submit" name="add_to_profile" class="btn btn-add">
                                            Add to Profile
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="already-added">✓ Already in your profile</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-skills">
                    <p>No skills available. Admins can add skills in the skill management section.</p>
                </div>
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