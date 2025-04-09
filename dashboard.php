<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Define fixed clubs as a constant array
const CLUBS = [
    ["club_id" => "1", "club_name" => "Robotics Club"],
    ["club_id" => "2", "club_name" => "Social Service Club"],
    ["club_id" => "3", "club_name" => "Game Club"]
];

// Initialize additional clubs in session if not already set
if (!isset($_SESSION['additional_clubs'])) {
    $_SESSION['additional_clubs'] = [];
}

// Combine all clubs for use throughout the script
$all_clubs = array_merge(CLUBS, $_SESSION['additional_clubs']);

// Handle adding a new club
if (isset($_POST['add-club'])) {
    $new_club_name = trim($_POST['new_club_name']);
    
    if (!empty($new_club_name)) {
        // Check if club name already exists
        $existing_names = array_column($all_clubs, 'club_name');
        if (in_array($new_club_name, $existing_names)) {
            $_SESSION['add_club_error'] = "Club name already exists!";
        } else {
            $club_ids = array_column($all_clubs, 'club_id');
            $new_club_id = (string)(max(array_map('intval', $club_ids)) + 1);
            
            $_SESSION['additional_clubs'][] = [
                "club_id" => $new_club_id,
                "club_name" => $new_club_name
            ];
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $_SESSION['add_club_error'] = "Please enter a valid club name!";
    }
}

// Handle role selection form submission
if (isset($_POST['select-role'])) {
    $selected_club_id = $_POST['club_id'];
    $is_executive = isset($_POST['role']) && $_POST['role'] === 'executive';

    $club_ids = array_column($all_clubs, 'club_id');
    if (in_array($selected_club_id, $club_ids)) {
        $_SESSION['selected_club_id'] = $selected_club_id;
        header("Location: " . ($is_executive ? "clubleads.php" : "general.php"));
        exit();
    } else {
        $_SESSION['role_error'] = "Please select a valid club!";
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Get student ID from session
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : "Unknown";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Club Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* [Your existing CSS remains unchanged] */
        body {
            background: #e6f0fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            max-width: 600px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .dashboard-container:hover {
            transform: translateY(-5px);
        }
        h3, h4 {
            font-weight: 700;
            color: #333;
            text-align: center;
        }
        h3 {
            margin-bottom: 1.5rem;
        }
        h4 {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6e8efb;
            box-shadow: 0 0 8px rgba(110, 142, 251, 0.6);
            transform: scale(1.02);
        }
        .btn-primary {
            background: #6e8efb;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #5a75d9;
            transform: scale(1.05);
        }
        .table {
            margin-top: 1rem;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 0.75rem;
            text-align: center;
        }
        .table thead {
            background: #6e8efb;
            color: white;
        }
        .btn-danger {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        .alert {
            border-radius: 8px;
            margin-top: 1rem;
        }
        #clock {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 1rem;
            transition: color 1s ease;
        }
        .form-check-label {
            margin-left: 0.5rem;
        }
    </style>
    <script>
        /* [Your existing JavaScript remains unchanged] */
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
    <div class="dashboard-container">
        <h3>Welcome, Student</h3>
        
        <form action="dashboard.php" method="post">
            <div class="mb-3">
                <label for="club_id" class="form-label">Select Your Club</label>
                <select class="form-select" id="club_id" name="club_id" required>
                    <option value="">-- Choose a Club --</option>
                    <?php foreach ($all_clubs as $club): ?>
                        <option value="<?php echo htmlspecialchars($club['club_id']); ?>">
                            <?php echo htmlspecialchars($club['club_id'] . " - " . $club['club_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="role" name="role" value="executive">
                <label class="form-check-label" for="role">I am a Club Lead</label>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="select-role">Proceed</button>
        </form>

        <?php if (isset($_SESSION['role_error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['role_error']); unset($_SESSION['role_error']); ?>
            </div>
        <?php endif; ?>

        <h4>Add a New Club</h4>
        <form action="dashboard.php" method="post">
            <div class="mb-3">
                <label for="new_club_name" class="form-label">Club Name</label>
                <input type="text" class="form-control" id="new_club_name" name="new_club_name" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="add-club">Add Club</button>
        </form>

        <?php if (isset($_SESSION['add_club_error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['add_club_error']); unset($_SESSION['add_club_error']); ?>
            </div>
        <?php endif; ?>

        <h4>Available Clubs</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Club ID</th>
                    <th>Club Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_clubs as $club): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($club['club_id']); ?></td>
                        <td><?php echo htmlspecialchars($club['club_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>