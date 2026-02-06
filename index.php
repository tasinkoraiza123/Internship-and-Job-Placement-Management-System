<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] == 'student') {
    header("Location: student_dashboard.php");
    exit(); 
} elseif ($_SESSION['role'] == 'company') {
    header("Location: company_dashboard.php");
    exit(); 
} elseif ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$students = 0;
$companies = 0;
$jobs = 0;
$applications = 0; 

$students = getCount($conn, 'students');
$companies = getCount($conn, 'companies');
$jobs = getCount($conn, 'jobs');
$events = getCount($conn, 'events');
$applications = getCount($conn, 'applications');

$recent_apps = $conn->query("
    SELECT a.application_id, a.applied_date, a.interview_date
    FROM applications a
    ORDER BY a.applied_date DESC
    LIMIT 5
");

function getCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    return 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship & Job Placement Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 4px solid #3498db;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }

        .stat-card:nth-child(2) { border-top-color: #2ecc71; }
        .stat-card:nth-child(3) { border-top-color: #e74c3c; }
        .stat-card:nth-child(4) { border-top-color: #f39c12; }
        .stat-card:nth-child(5) { border-top-color: #9b59b6; } /* Added for applications */

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #2c3e50;
            line-height: 1;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #7f8c8d;
            font-weight: 600;
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }

        .nav-btn {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .nav-btn:hover {
            background: #3498db;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            border-color: #3498db;
        }

        .alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert a {
            color: #856404;
            font-weight: bold;
            text-decoration: underline;
        }

        .table-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }


        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            table {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Internship & Job Placement Management System</h1>
            <p>Welcome, <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>!</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $students; ?></div>
                <div class="stat-label">Students</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $companies; ?></div>
                <div class="stat-label">Companies</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $jobs; ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $events; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $applications; ?></div>
                <div class="stat-label">Applications</div>
            </div>
        </div>

        <div class="nav-grid">
            <a href="student_management.php" class="nav-btn">Students</a>
            <a href="company_management.php" class="nav-btn">Companies</a>
            <a href="posted_jobs.php" class="nav-btn">Jobs</a>
            <a href="application_update.php" class="nav-btn">Applications</a>
            <a href="event_management.php" class="nav-btn">Events</a>
            <a href="skill_management.php" class="nav-btn">Skills</a>
            <a href="interview_management.php" class="nav-btn">Interviews</a>
            <a href="placement_management.php" class="nav-btn">Placements</a>
            <a href="logout.php" class="nav-btn" style="background: #e7b93c73; color: black;">Logout</a>
        </div>


        <div class="footer">
            <p>Internship & Job Placement Management System &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</body>
</html>