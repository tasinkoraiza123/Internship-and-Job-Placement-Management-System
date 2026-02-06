<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$success_msg = '';
$error_msg = '';

$check_column = $conn->query("SHOW COLUMNS FROM event_attendance LIKE 'student_id'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE event_attendance ADD COLUMN student_id VARCHAR(20)");
    $conn->query("ALTER TABLE event_attendance ADD FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_event'])) {
    $event_id = (int)$_POST['event_id'];
    
    $event_check = $conn->query("SELECT * FROM events WHERE event_id = $event_id");
    if ($event_check->num_rows > 0) {
        $event = $event_check->fetch_assoc();
        
        if (strtotime($event['event_date']) < strtotime(date('Y-m-d'))) {
            $error_msg = "Cannot book past events.";
        }
        elseif ($conn->query("SELECT * FROM event_attendance WHERE event_id = $event_id AND student_id = '$user_id'")->num_rows > 0) {
            $error_msg = "You have already booked this event.";
        }
        else {
            $attendance_count = $conn->query("SELECT COUNT(*) as count FROM event_attendance WHERE event_id = $event_id")->fetch_assoc()['count'];
            if ($attendance_count >= $event['max_attendees']) {
                $error_msg = "Event is full.";
            } else {
                $sql = "INSERT INTO event_attendance (event_id, student_id) VALUES ($event_id, '$user_id')";
                if ($conn->query($sql)) {
                    $success_msg = "Booking successful!";
                } else {
                    $error_msg = "Error: " . $conn->error;
                }
            }
        }
    } else {
        $error_msg = "Event not found.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $event_id = (int)$_POST['event_id'];
    $sql = "DELETE FROM event_attendance WHERE event_id = $event_id AND student_id = '$user_id'";
    if ($conn->query($sql)) {
        $success_msg = "Booking cancelled.";
    } else {
        $error_msg = "Error: " . $conn->error;
    }
}

$events = $conn->query("
    SELECT e.*, 
           (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.event_id) as booked,
           (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.event_id AND student_id = '$user_id') as user_booked
    FROM events e 
    ORDER BY e.event_date ASC
");

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Events - Book Seat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
        .alert { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #3498db; color: white; padding: 12px 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-book { background: #27ae60; color: white; }
        .btn-cancel { background: #e74c3c; color: white; }
        .btn-disabled { background: #95a5a6; color: white; cursor: not-allowed; }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; display: inline-block; }
        .upcoming { background: #d4edda; color: #155724; }
        .past { background: #f8d7da; color: #721c24; }
        .capacity-info { font-size: 13px; margin-top: 5px; }
        .full { color: #e74c3c; font-weight: bold; }
        .available { color: #27ae60; font-weight: bold; }
        .booked-status { color: #3498db; font-weight: bold; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="student_dashboard.php" class="dashboard-link">← Back to Dashboard</a>
        <h1>Events</h1>
        
        <?php if ($success_msg): ?><div class="alert success"><?php echo $success_msg; ?></div><?php endif; ?>
        <?php if ($error_msg): ?><div class="alert error"><?php echo $error_msg; ?></div><?php endif; ?>
        
        <?php if ($events && $events->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Your Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($event = $events->fetch_assoc()): 
                        $is_past = strtotime($event['event_date']) < strtotime($today);
                        $user_booked = $event['user_booked'] > 0;
                        $is_full = $event['booked'] >= $event['max_attendees'];
                        $seats_left = $event['max_attendees'] - $event['booked'];
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                            
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($event['event_date'])); ?><br>
                            <span class="status-badge <?php echo $is_past ? 'past' : 'upcoming'; ?>">
                                <?php echo $is_past ? 'Past' : 'Upcoming'; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if ($event['street_no'] && $event['city']) {
                                echo "St. " . $event['street_no'] . ", " . htmlspecialchars($event['city']);
                            } elseif ($event['city']) {
                                echo htmlspecialchars($event['city']);
                            } else {
                                echo "St. " . $event['street_no'];
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo $event['max_attendees']; ?> seats<br>
                            <span class="capacity-info <?php echo $is_full ? 'full' : 'available'; ?>">
                                <?php echo $is_full ? 'Full' : $seats_left . ' seats left'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user_booked): ?>
                                <span class="booked-status">Booked</span>
                            <?php else: ?>
                                <span style="color:#666;">Not booked</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($role === 'student'): ?>
                                <?php if ($is_past): ?>
                                    <button class="btn btn-disabled" disabled>Past</button>
                                <?php elseif ($user_booked): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <button type="submit" name="cancel_booking" class="btn btn-cancel" 
                                                onclick="return confirm('Cancel booking?')">Cancel</button>
                                    </form>
                                <?php elseif ($is_full): ?>
                                    <button class="btn btn-disabled" disabled>Full</button>
                                <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <button type="submit" name="book_event" class="btn btn-book" 
                                                onclick="return confirm('Book seat?')">Book</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#666;">Student only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding:40px; color:#666;">No events scheduled.</p>
        <?php endif; ?>
    </div>
    
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(msg => msg.style.display = 'none');
        }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>