<?php
session_start();
require_once 'config.php';

// Check if company is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: login.php");
    exit();
}

// Get company info
$company_id = $_SESSION['user_id'];
$sql = "SELECT * FROM companies WHERE company_id = '$company_id'";
$result = $conn->query($sql);
$company = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Company Dashboard</title>
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
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
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
            background: #3498db;
            color: white; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .menu a:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        .company-info {
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
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
                <h1 class="welcome">Welcome, <?php echo htmlspecialchars($company['name']); ?>!</h1>
                <h1 class="company">Company</h1>
            </div>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        
        <div class="company-info">
            <h3>Company Information</h3>
            <div class="info-grid">
                <div><strong>Company Name:</strong> <?php echo htmlspecialchars($company['name']); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($company['email']); ?></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($company['phone'] ?: 'Not Set'); ?></div>
                <div><strong>Description:</strong> <?php echo htmlspecialchars($company['description'] ?: 'No Description'); ?></div>
            </div>
        </div>
        
        <div class="menu">
            <a href="job_management.php">Post Jobs</a>
            <a href="application_update.php">Applications</a>
            <a href="placement_management.php">Placements</a>
            <a href="company.php">Company</a>
        </div>
    </div>
</body>
</html>