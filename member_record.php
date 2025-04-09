<?php
session_start();

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Initialize member_records array if not already set
if (!isset($_SESSION['member_records'])) {
    $_SESSION['member_records'] = [];
}

// Function to generate a unique record ID
function generateRecordId() {
    return 'REC' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Sync selected members from clubregistration.php to member_records.php
if (isset($_SESSION['members'])) {
    foreach ($_SESSION['members'] as $member) {
        if ($member['status'] === 'Selected') {
            $memberExists = false;
            foreach ($_SESSION['member_records'] as $record) {
                if ($record['first_name'] === $member['first_name'] &&
                    $record['middle_name'] === $member['middle_name'] &&
                    $record['last_name'] === $member['last_name'] &&
                    $record['dob'] === $member['dob']) {
                    $memberExists = true;
                    break;
                }
            }
            if (!$memberExists) {
                $_SESSION['member_records'][] = [
                    'record_id' => generateRecordId(),
                    'first_name' => $member['first_name'],
                    'middle_name' => $member['middle_name'],
                    'last_name' => $member['last_name'],
                    'dob' => $member['dob'],
                    'club_id' => $member['club_id'],
                    'activity_status' => 'Active' // Default status for new members
                ];
            }
        }
    }
}

// Handle activity status update
if (isset($_POST['update_activity'])) {
    $index = $_POST['record_index'];
    $new_activity_status = $_POST['activity_status'];
    
    if (isset($_SESSION['member_records'][$index]) && 
        in_array($new_activity_status, ['Active', 'Moderate', 'Less Active'])) {
        $_SESSION['member_records'][$index]['activity_status'] = $new_activity_status;
    }
    header("Location: member_record.php"); // Refresh page
    exit();
}

$member_records = $_SESSION['member_records'];
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
        .status-less-active {
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
        .btn-danger {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        #clock {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 1rem;
            transition: color 1s ease;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function updateClock() {
                const now = new Date();
                const options = { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit' 
                };
                const clock = document.getElementById('clock');
                if (clock) {
                    clock.textContent = now.toLocaleString('en-US', options);
                    const hour = now.getHours();
                    if (hour >= 6 && hour < 12) {
                        clock.style.color = "#6e8efb";
                    } else if (hour >= 12 && hour < 18) {
                        clock.style.color = "#ff6f61";
                    } else {
                        clock.style.color = "#a777e3";
                    }
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
        
        <?php if (empty($member_records)): ?>
            <p class="text-center">No selected members found.</p>
        <?php else: ?>
            <?php foreach ($member_records as $index => $record): ?>
                <div class="record-box">
                    <div class="member-info">
                        <p class="info-text">Record ID: <?php echo htmlspecialchars($record['record_id']); ?></p>
                        <p class="info-text">Name: <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['middle_name'] . ' ' . $record['last_name']); ?></p>
                        <p class="info-text">Date of Birth: <?php echo htmlspecialchars($record['dob']); ?></p>
                        <p class="info-text">Club ID: <?php echo htmlspecialchars($record['club_id']); ?></p>
                    </div>
                    <form class="status-form" method="post" action="member_record.php">
                        <select name="activity_status" class="activity-select 
                            <?php 
                            echo $record['activity_status'] === 'Active' ? 'status-active' : 
                                ($record['activity_status'] === 'Moderate' ? 'status-moderate' : 'status-less-active'); 
                            ?>">
                            <option value="Active" <?php echo $record['activity_status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Moderate" <?php echo $record['activity_status'] === 'Moderate' ? 'selected' : ''; ?>>Moderate</option>
                            <option value="Less Active" <?php echo $record['activity_status'] === 'Less Active' ? 'selected' : ''; ?>>Less Active</option>
                        </select>
                        <input type="hidden" name="record_index" value="<?php echo $index; ?>">
                        <button type="submit" name="update_activity" class="btn-update">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>