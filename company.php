<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['user_id'];

$current_company = $conn->query("SELECT * FROM companies WHERE company_id = '$company_id'")->fetch_assoc();

$sql = "SELECT * FROM companies ORDER BY company_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <a href="company_dashboard.php" class="dashboard-link">← Back to Dashboard</a>
    <title>Companies List</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #f0f2f5; 
            margin: 0; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .header { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-btn { 
            background: #6c757d; 
            color: white; 
            padding: 8px 20px; 
            text-decoration: none; 
            border-radius: 5px;
        }
        .company-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        .company-table th {
            background: #3498db;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .company-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .company-table tr:last-child td {
            border-bottom: none;
        }
        .company-table tr:hover {
            background: #f9f9f9;
        }
        .current-company {
            background: #f0f8ff !important;
            font-weight: bold;
        }
        .badge {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 5px;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .description-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .description-cell:hover {
            white-space: normal;
            overflow: visible;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Company List</h2>
           
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="company-table">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="25%">Email</th>
                        <th width="20%">Phone</th>
                        <th width="40%">Description</th>
                        <th width="10%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): 
                        $is_current = $row['company_id'] == $company_id;
                    ?>
                    <tr class="<?php echo $is_current ? 'current-company' : ''; ?>">
                        <td><?php echo htmlspecialchars($row['company_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                        <td class="description-cell"><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                        <td>
                            <?php if($is_current): ?>
                                <span class="badge">You</span>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No companies are registered in the system yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>