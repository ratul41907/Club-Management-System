<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Instead of redirecting to itself, we should show the login form
    // Remove this redirect to prevent the loop
    // header("Location: index.php");
    // exit();
    
    // The login form should be displayed below in the HTML
    $show_login_form = true;
} else {
    $show_login_form = false;
    
    // Initialize predefined clubs array in session if not already set
    if (!isset($_SESSION['clubs'])) {
        $_SESSION['clubs'] = [
            ["club_id" => "1", "club_name" => "Robotics Club"],
            ["club_id" => "2", "club_name" => "Social Service Club"],
            ["club_id" => "3", "club_name" => "Game Club"]
        ];
    }
    
    // Handle club deletion
    if (isset($_GET['delete_club_id'])) {
        $delete_id = $_GET['delete_club_id'];
        foreach ($_SESSION['clubs'] as $key => $club) {
            if ($club['club_id'] === $delete_id) {
                unset($_SESSION['clubs'][$key]);
                // Reindex array to avoid gaps in keys
                $_SESSION['clubs'] = array_values($_SESSION['clubs']);
                header("Location: dashboard.php");
                exit();
            }
        }
    }
    
    // Handle role selection form submission
    if (isset($_POST['select-role'])) {
        $selected_club_id = $_POST['club_id'];
        $is_executive = isset($_POST['role']) && $_POST['role'] === 'executive';
    
        // Validate selected club ID
        $club_ids = array_column($_SESSION['clubs'], 'club_id');
        if (in_array($selected_club_id, $club_ids)) {
            $_SESSION['selected_club_id'] = $selected_club_id;
            if ($is_executive) {
                header("Location: clubleads.php"); // Changed from executive.php to clubleads.php
            } else {
                header("Location: general.php");
            }
            exit();
        } else {
            $_SESSION['role_error'] = "Please select a valid club!";
        }
    }
}

// Handle login form submission
if (isset($_POST['login'])) {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];
    
    // Simple validation - in a real app, you'd check against a database
    if (!empty($student_id) && !empty($password)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['student_id'] = $student_id;
        // Redirect to the same page, but now the user is logged in
        header("Location: Index.php");
        exit();
    } else {
        $login_error = "Please enter both Student ID and Password!";
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: Index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_login_form ? 'Login' : 'Dashboard'; ?> - Student Club Management System</title>
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
        .container {
            max-width: 600px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .container:hover {
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
        .btn-delete {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            width: auto;
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
    <div class="container <?php echo $show_login_form ? 'login-container' : 'dashboard-container'; ?>">
        <?php if ($show_login_form): ?>
            <!-- Login Form -->
            <h3>Student Login</h3>
            <form action="Index.php" method="post">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100" name="login">Login</button>
            </form>
            
            <?php if (isset($login_error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Dashboard Content -->
            <h3>Welcome, Student <?php echo htmlspecialchars($_SESSION['student_id']); ?>!</h3>
            
            <form action="Index.php" method="post">
                <div class="mb-3">
                    <label for="club_id" class="form-label">Select Your Club</label>
                    <select class="form-select" id="club_id" name="club_id" required>
                        <option value="">-- Choose a Club --</option>
                        <?php foreach ($_SESSION['clubs'] as $club): ?>
                            <option value="<?php echo htmlspecialchars($club['club_id']); ?>">
                                <?php echo htmlspecialchars($club['club_id'] . " - " . $club['club_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="role" name="role" value="executive">
                    <label class="form-check-label" for="role">I am an Executive Member</label>
                </div>
                <button type="submit" class="btn btn-primary w-100" name="select-role">Proceed</button>
            </form>

            <?php if (isset($_SESSION['role_error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($_SESSION['role_error']); unset($_SESSION['role_error']); ?>
                </div>
            <?php endif; ?>

            <h4>Available Clubs</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Club ID</th>
                        <th>Club Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['clubs'] as $club): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($club['club_id']); ?></td>
                            <td><?php echo htmlspecialchars($club['club_name']); ?></td>
                            <td>
                                <a href="Index.php?delete_club_id=<?php echo urlencode($club['club_id']); ?>" 
                                   class="btn btn-danger btn-delete"
                                   onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($club['club_name']); ?>?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href="Index.php?logout=true" class="btn btn-danger">Logout</a>
        <?php endif; ?>
        
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>