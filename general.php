<?php
session_start();

// Check if user is logged in and has selected a club
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['selected_club_id'])) {
    header("Location: index.php");
    exit();
}

// Handle member login form submission
if (isset($_POST['member_login'])) {
    $member_id = trim($_POST['member_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Hardcoded credentials for demo (replace with database check in production)
    $valid_member_id = "member123";
    $valid_password = "pass456";

    if ($member_id === $valid_member_id && $password === $valid_password) {
        $_SESSION['member_logged_in'] = true;
        $_SESSION['member_id'] = $member_id;
        header("Location: member_dashboard.php"); // Redirect to a member dashboard
        exit();
    } else {
        $login_error = "Invalid Member ID or Password!";
    }
}

// Get selected club details
$selected_club_id = $_SESSION['selected_club_id'];
$all_clubs = array_merge(
    [
        ["club_id" => "1", "club_name" => "Robotics Club"],
        ["club_id" => "2", "club_name" => "Social Service Club"],
        ["club_id" => "3", "club_name" => "Game Club"]
    ],
    $_SESSION['additional_clubs'] ?? []
);
$selected_club = current(array_filter($all_clubs, function($club) use ($selected_club_id) {
    return $club['club_id'] === $selected_club_id;
}));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login - <?php echo htmlspecialchars($selected_club['club_name']); ?></title>
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
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        h3 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #6e8efb;
            box-shadow: 0 0 8px rgba(110, 142, 251, 0.6);
        }
        .btn-primary {
            background: #6e8efb;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover {
            background: #5a75d9;
        }
        .alert {
            border-radius: 8px;
            margin-top: 1rem;
        }
        .club-info {
            text-align: center;
            margin-bottom: 1rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3>Member Login</h3>
        <div class="club-info">
            Club: <?php echo htmlspecialchars($selected_club['club_name']); ?>
        </div>

        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form action="general.php" method="post">
            <div class="mb-3">
                <label for="member_id" class="form-label">Member ID</label>
                <input type="text" class="form-control" id="member_id" name="member_id" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="member_login">Login</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>