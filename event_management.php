<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $type = $conn->real_escape_string($_POST['type']);
        $event_date = $_POST['event_date'];
        $street_no = (int)$_POST['street_no'];
        $city = $conn->real_escape_string($_POST['city']);
        $max_attendees = (int)$_POST['max_attendees'];
        
        $sql = "INSERT INTO events (title, type, event_date, street_no, city, max_attendees) 
                VALUES ('$title', '$type', '$event_date', $street_no, '$city', $max_attendees)";
        
        if ($conn->query($sql)) {
            $success_msg = "Event added successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete'])) {
        $event_id = (int)$_POST['event_id'];
        $sql = "DELETE FROM events WHERE event_id = $event_id";
        
        if ($conn->query($sql)) {
            $success_msg = "Event deleted successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
}
$events = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .form input {
            margin-bottom: 15px;
        }
        
        .address-group {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .address-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="dashboard-link">← Back to Dashboard</a>
        <h1>Event Management</h1>
        
        <?php if ($success_msg): ?>
            <div class="alert success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Event</h2>
            <form method="POST" class="form">
                <input type="text" name="title" placeholder="Event Title" required>
                <input type="text" name="type" placeholder="Event Type (e.g., Workshop, Seminar)">
                <input type="date" name="event_date" required>
                
                <div class="address-group">
                    <input type="number" name="street_no" placeholder="Street Number" min="1">
                    <input type="text" name="city" placeholder="City">
                </div>
                
                <input type="number" name="max_attendees" placeholder="Maximum Attendees" min="1" value="100">
                <button type="submit" name="add">Add Event</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Event List</h2>
            <?php if ($events && $events->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Street No</th>
                        <th>City</th>
                        <th>Max Attendees</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while($row = $events->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                        <td><?php echo $row['street_no']; ?></td>
                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                        <td><?php echo $row['max_attendees']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>">
                                <button type="submit" name="delete" class="btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this event?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">No events found. Add your first event above.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Clear messages after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
<?php
$conn->close();
?>