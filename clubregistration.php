<?php
session_start();

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Function to calculate age from DOB
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

// Ensure members array exists in session (from recruitment.php)
if (!isset($_SESSION['members'])) {
    $_SESSION['members'] = [
        [
            'first_name' => 'Karim',
            'middle_name' => 'Rahim',
            'last_name' => 'Sami',
            'dob' => '1998-05-15',
            'club_id' => '1',
            'status' => 'Pending'
        ],
        [
            'first_name' => 'Siam',
            'middle_name' => 'Ahmed',
            'last_name' => 'Siam',
            'dob' => '2000-09-22',
            'club_id' => '2',
            'status' => 'Pending'
        ],
        [
            'first_name' => 'Sakib',
            'middle_name' => 'al',
            'last_name' => 'Hasan',
            'dob' => '1997-12-03',
            'club_id' => '3',
            'status' => 'Pending'
        ],
        [
            'first_name' => 'Md',
            'middle_name' => 'SK',
            'last_name' => 'Rasel',
            'dob' => '1999-03-10',
            'club_id' => '1',
            'status' => 'Pending'
        ],
        [
            'first_name' => 'Md',
            'middle_name' => 'Abdur',
            'last_name' => 'Rahim',
            'dob' => '2001-07-28',
            'club_id' => '2',
            'status' => 'Pending'
        ]
    ];
}

// Handle status change
if (isset($_POST['update_status'])) {
    $index = $_POST['member_index'];
    $new_status = $_POST['status'];
    
    if (isset($_SESSION['members'][$index]) && in_array($new_status, ['Selected', 'Pending'])) {
        $_SESSION['members'][$index]['status'] = $new_status;
    }
    header("Location: clubregistration.php"); // Refresh page
    exit();
}

$members = $_SESSION['members'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Registration - Student Club Management System</title>
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
        .registration-container {
            max-width: 900px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .registration-container:hover {
            transform: translateY(-5px);
        }
        h3 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        .registration-box {
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
        .registration-box:hover {
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
        .status-select {
            width: 120px;
            padding: 0.25rem;
            border-radius: 4px;
            border: none;
            color: white;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        .status-selected {
            background: #28a745;
        }
        .status-pending {
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
        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 0.5rem;
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
    <div class="registration-container">
        <h3>Club Registration</h3>
        
        <?php foreach ($members as $index => $member): ?>
            <div class="registration-box">
                <div class="member-info">
                    <p class="info-text">Name: <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name']); ?></p>
                    <p class="info-text">Date of Birth: <?php echo htmlspecialchars($member['dob']); ?></p>
                    <p class="info-text">Age: <?php echo calculateAge($member['dob']); ?></p>
                    <p class="info-text">Club ID: <?php echo htmlspecialchars($member['club_id']); ?></p>
                </div>
                <form class="status-form" method="post" action="clubregistration.php">
                    <select name="status" class="status-select <?php echo ($member['status'] ?? 'Pending') === 'Selected' ? 'status-selected' : 'status-pending'; ?>">
                        <option value="Selected" <?php echo ($member['status'] ?? 'Pending') === 'Selected' ? 'selected' : ''; ?>>Selected</option>
                        <option value="Pending" <?php echo ($member['status'] ?? 'Pending') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                    <input type="hidden" name="member_index" value="<?php echo $index; ?>">
                    <button type="submit" name="update_status" class="btn-update">Update</button>
                </form>
            </div>
        <?php endforeach; ?>

        <a href="member_record.php" class="btn btn-primary">View Member Records</a>
        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>