<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$sql = "SELECT s.*, d.dept_name 
        FROM students s 
        LEFT JOIN departments d ON s.dept_id = d.dept_id 
        WHERE s.student_id = '$student_id'";
$result = $conn->query($sql);
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            margin-bottom: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome { color: #333; font-size: 28px; margin: 0; }
        .role-badge { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 8px 20px; 
            border-radius: 20px; 
            font-size: 16px;
            font-weight: bold;
        }
        .menu { 
            display: flex; 
            gap: 15px; 
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .menu a { 
            background: #667eea;
            color: white; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .menu a:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .content { 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .logout { 
            background: #dc3545; 
            color: white; 
            padding: 10px 25px; 
            text-decoration: none; 
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .logout:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="welcome">Welcome, <?php echo htmlspecialchars($student['f_name']); ?>!</h1>
                <h1 class="student">Student </h1>
            </div>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        
        <div class="student-info">
            <h3>Student Information</h3>
            <div class="info-grid">
                <div><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></div>
                <div><strong>Name:</strong> <?php echo htmlspecialchars($student['f_name'] . ' ' . $student['l_name']); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></div>
                <div><strong>Department:</strong> <?php echo htmlspecialchars($student['dept_name']); ?></div>
                <div><strong>CGPA:</strong> <?php echo $student['cgpa'] ? htmlspecialchars($student['cgpa']) : 'Not Set'; ?></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></div>
            </div>
        </div>
        
         <div class="menu">
         <a href="application_management.php">Applications</a>
         <a href="posted_jobs.php">Job List</a>
         <a href="events.php">Events</a>
         <a href="skills.php">Skills</a>
         </div>
    </div>
</body>
</html>