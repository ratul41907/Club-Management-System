<?php
session_start();

// Include database connection
require_once 'db_connect.php';

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: menu.php");
    exit();
}

// Sync members from club_membership to member_records
$club_id = $_SESSION['selected_club_id'] ?? 'N/A';
if ($club_id !== 'N/A') {
    // Fetch all Member_id from club_membership for the selected club
    $stmt = $conn->prepare("SELECT Member_id FROM club_membership WHERE club_id = ?");
    $stmt->bind_param("s", $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member_ids = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Ensure each Member_id has a corresponding member_records entry
    foreach ($member_ids as $member) {
        $record_id = $member['Member_id'];
        // Check if record exists
        $stmt = $conn->prepare("SELECT record_id FROM member_records WHERE record_id = ?");
        $stmt->bind_param("s", $record_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Insert new record with default activity status
            $stmt = $conn->prepare("INSERT INTO member_records (record_id, activity) VALUES (?, ?)");
            $default_activity = 'Active';
            $stmt->bind_param("ss", $record_id, $default_activity);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// Handle activity status update
if (isset($_POST['update_activity'])) {
    $record_id = filter_input(INPUT_POST, 'record_id', FILTER_SANITIZE_STRING);
    $new_activity_status = filter_input(INPUT_POST, 'activity_status', FILTER_SANITIZE_STRING);
    
    if (!empty($record_id) && in_array($new_activity_status, ['Active', 'Moderate', 'Inactive'])) {
        // Update activity status
        $stmt = $conn->prepare("UPDATE member_records SET activity = ? WHERE record_id = ?");
        $stmt->bind_param("ss", $new_activity_status, $record_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $success_message = "Activity status updated successfully!";
            } else {
                $error_message = "No record found with this ID.";
            }
        } else {
            $error_message = "Error updating activity status: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please provide valid inputs.";
    }
}

// Fetch member records for the selected club
$member_records = [];
if ($club_id !== 'N/A') {
    $stmt = $conn->prepare("
        SELECT mr.record_id, mr.activity 
        FROM member_records mr
        JOIN club_membership cm ON mr.record_id = cm.Member_id
        WHERE cm.club_id = ?
    ");
    $stmt->bind_param("s", $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member_records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Records - Student Club Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { 
            background: #e6f0fa; 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .records-container { 
            max-width: 900px; 
            width: 100%; 
            padding: 2rem; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); 
            transition: transform 0.3s ease; 
        }
        .records-container:hover { 
            transform: translateY(-5px); 
        }
        h3 { 
            font-weight: 700; 
            color: #333; 
            text-align: center; 
            margin-bottom: 2rem; 
        }
        .record-box { 
            background: #f8f9fa; 
            border: 1px solid #6e8efb; 
            border-radius: 8px; 
            padding: 1rem; 
            margin-bottom: 1rem; 
            transition: all 0.3s ease; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .record-box:hover { 
            transform: scale(1.02); 
            box-shadow: 0 4px 15px rgba(110, 142, 251, 0.2); 
        }
        .member-info { 
            flex-grow: 1; 
        }
        .info-text { 
            margin: 0.25rem 0; 
            color: #333; 
        }
        .status-form { 
            display: flex; 
            align-items: center; 
        }
        .activity-select { 
            width: 120px; 
            padding: 0.25rem; 
            border-radius: 4px; 
            border: none; 
            color: white; 
            font-weight: 600; 
            margin-right: 0.5rem; 
        }
        .status-active { 
            background: #28a745; 
        }
        .status-moderate { 
            background: #ffc107; 
        }
        .status-inactive { 
            background: #dc3545; 
        }
        .btn-update { 
            padding: 0.25rem 0.75rem; 
            background: #6e8efb; 
            border: none; 
            border-radius: 4px; 
            color: white; 
            font-weight: 600; 
            transition: all 0.3s ease; 
        }
        .btn-update:hover { 
            background: #5a75d9; 
            transform: scale(1.05); 
        }
        .btn-danger, .btn-primary { 
            width: 100%; 
            padding: 0.75rem; 
            border-radius: 8px; 
            font-weight: 600; 
            margin-top: 0.5rem; 
        }
        .btn-danger { 
            margin-top: 1.5rem; 
        }
        #clock { 
            text-align: center; 
            margin-top: 1.5rem; 
            font-size: 1rem; 
            transition: color 1s ease; 
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function updateClock() {
                const now = new Date();
                const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                const clock = document.getElementById('clock');
                if (clock) {
                    clock.textContent = now.toLocaleString('en-US', options);
                    const hour = now.getHours();
                    if (hour >= 6 && hour < 12) clock.style.color = "#6e8efb";
                    else if (hour >= 12 && hour < 18) clock.style.color = "#ff6f61";
                    else clock.style.color = "#a777e3";
                }
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
</head>
<body>
    <div class="records-container">
        <h3>Member Records</h3>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($member_records)): ?>
            <p class="text-center">No selected members found.</p>
        <?php else: ?>
            <?php foreach ($member_records as $record): ?>
                <div class="record-box">
                    <div class="member-info">
                        <p class="info-text">Record ID: <?php echo htmlspecialchars($record['record_id']); ?></p>
                    </div>
                    <form class="status-form" method="post" action="member_record.php">
                        <select name="activity_status" class="activity-select 
                            <?php echo $record['activity'] === 'Active' ? 'status-active' : 
                                ($record['activity'] === 'Moderate' ? 'status-moderate' : 'status-inactive'); ?>">
                            <option value="Active" <?php echo $record['activity'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Moderate" <?php echo $record['activity'] === 'Moderate' ? 'selected' : ''; ?>>Moderate</option>
                            <option value="Inactive" <?php echo $record['activity'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record['record_id']); ?>">
                        <button type="submit" name="update_activity" class="btn-update">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="menu.php" class="btn btn-primary">Back to Club Lead Menu</a>
        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>