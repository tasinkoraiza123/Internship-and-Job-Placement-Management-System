<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$filter_job = isset($_GET['job_id']) ? intval($_GET['job_id']) : null;
$filter_status = isset($_GET['status']) ? $_GET['status'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "
    SELECT 
        a.application_id,
        a.job_id,
        a.applied_date,
        a.interview_date,
        a.offer_salary,
        j.title as job_title,
        j.salary as job_salary,
        c.name as company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN companies c ON j.company_id = c.company_id
    WHERE 1=1
";

if ($filter_job) {
    $query .= " AND a.job_id = $filter_job";
}

if ($filter_status) {
    if ($filter_status == 'interview_scheduled') {
        $query .= " AND a.interview_date IS NOT NULL AND a.offer_salary IS NULL";
    } elseif ($filter_status == 'offer_made') {
        $query .= " AND a.offer_salary IS NOT NULL";
    } elseif ($filter_status == 'pending') {
        $query .= " AND a.interview_date IS NULL AND a.offer_salary IS NULL";
    }
}

if (!empty($search)) {
    $query .= " AND (j.title LIKE '%$search%' OR c.name LIKE '%$search%')";
}

$query .= " ORDER BY a.applied_date DESC";

$applications = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Applications</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            align-items: end;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #040b57a4;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-success {
            background: #080808ff;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-interview {
            background: #d1ecf1;
            color: #0e0f0fff;
        }
        .status-offer {
            background: #d4edda;
            color: #153f1dff;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .actions a {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            text-decoration: none;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: #d1ecf1;
            color: #080808ff;
            border: 1px solid #bee5eb;
        }
        .count-badge {
            background: #101010ff;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .view-link {
            color: #0f1010ff;
            text-decoration: none;
        }
        .view-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Applications</h1>
            
        </div>


        
            <?php if ($applications->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
    <tr>
        <th>App ID</th>
        <th>Job ID</th>
        <th>Job Title</th>
        <th>Company</th>
        <th>Applied Date</th>
        <th>Interview Date</th>
        <th>Offer Salary</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
    <?php 
    $counter = 1;
    while($app = $applications->fetch_assoc()):
        $status = 'Pending';
        $status_class = 'status-pending';
        
        if ($app['offer_salary']) {
            $status = 'Offer Made';
            $status_class = 'status-offer';
        } elseif ($app['interview_date']) {
            $status = 'Interview Scheduled';
            $status_class = 'status-interview';
        }
    ?>
    <tr>
        <td><?php echo $counter++; ?></td>
        <td><?php echo $app['job_id']; ?></td>
        <td>
            <a href="application_update.php?job_id=<?php echo $app['job_id']; ?>" class="view-link">
                <?php echo htmlspecialchars($app['job_title']); ?>
            </a>
        </td>
        <td><?php echo htmlspecialchars($app['company_name']); ?></td>
        <td><?php echo date('M d, Y', strtotime($app['applied_date'])); ?></td>
        <td>
            <?php if ($app['interview_date']): ?>
                <?php echo date('M d, Y', strtotime($app['interview_date'])); ?>
            <?php else: ?>
                <span style="color: #999; font-size: 13px;">Not scheduled</span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($app['offer_salary']): ?>
                <span style="color: #080808ff;"><?php echo $app['offer_salary']; ?></span>
            <?php else: ?>
                <span style="color: #999; font-size: 13px;">Not offered yet</span>
            <?php endif; ?>
        </td>
        <td>
            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">
                <p>No applications found.</p>
                <?php if ($filter_job || $filter_status || $search): ?>
                    <p>Try changing your filter criteria.</p>
                    <a href="application.php" class="btn btn-primary">Show All Applications</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        
    </div>

    <script>
    document.querySelectorAll('select[name="status"], input[name="job_id"]').forEach(element => {
        element.addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    });

    setInterval(function() {
        if (!document.hidden) {
            window.location.reload();
        }
    }, 30000);

    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().slice(0, 10);
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const interviewDate = row.cells[5].textContent;
            if (interviewDate.includes(today.slice(5, 7) + '/' + today.slice(8, 10))) {
                row.style.backgroundColor = '#fffde7';
            }
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>