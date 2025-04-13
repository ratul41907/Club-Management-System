<?php
session_start();


require_once 'db_connect.php';

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: menu.php"); // Redirect back to menu.php if not a lead
    exit();
}


if (isset($_POST['add_member'])) {
    $sl = filter_input(INPUT_POST, 'sl', FILTER_SANITIZE_STRING);
    $membership_type = filter_input(INPUT_POST, 'new_membership_type', FILTER_SANITIZE_STRING);
    $membership_amount = filter_input(INPUT_POST, 'membership_amount', FILTER_VALIDATE_INT);
    $club_id = $_SESSION['selected_club_id'] ?? 'N/A';

    if (!empty($sl) && in_array($membership_type, ['Yearly', 'Half Yearly']) && 
        in_array($membership_amount, [500, 1000]) && $club_id !== 'N/A') {
        // Check if member_id already exists
        $stmt = $conn->prepare("SELECT Member_id FROM club_membership WHERE Member_id = ?");
        $stmt->bind_param("s", $sl);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Member ID already exists.";
        } else {
            // Insert new member
            $stmt = $conn->prepare("INSERT INTO club_membership (Member_id, Member_type, member_fee, club_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $sl, $membership_type, $membership_amount, $club_id);

            if ($stmt->execute()) {
                $success_message = "Member added successfully!";
            } else {
                $error_message = "Error adding member: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        $error_message = "Please provide valid inputs.";
    }
}


if (isset($_POST['update_membership'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_STRING);
    $new_membership_type = filter_input(INPUT_POST, 'membership_type', FILTER_SANITIZE_STRING);
    
    if (!empty($member_id) && in_array($new_membership_type, ['Yearly', 'Half Yearly'])) {
        $new_membership_amount = ($new_membership_type === 'Yearly') ? 1000 : 500;
        
        // Update membership
        $stmt = $conn->prepare("UPDATE club_membership SET Member_type = ?, member_fee = ? WHERE Member_id = ?");
        $stmt->bind_param("sis", $new_membership_type, $new_membership_amount, $member_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $success_message = "Membership updated successfully!";
            } else {
                $error_message = "No member found with this ID.";
            }
        } else {
            $error_message = "Error updating membership: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please provide valid inputs.";
    }
}


$club_id = $_SESSION['selected_club_id'] ?? 'N/A';
$member_records = [];
if ($club_id !== 'N/A') {
    $stmt = $conn->prepare("SELECT Member_id, Member_type, member_fee, club_id FROM club_membership WHERE club_id = ?");
    $stmt->bind_param("s", $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member_records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}


$total_members = count($member_records);
$total_money = array_sum(array_column($member_records, 'member_fee'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Membership - Student Club Management System</title>
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
        .membership-container {
            max-width: 900px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .membership-container:hover {
            transform: translateY(-5px);
        }
        h3 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        .membership-box {
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
        .membership-box:hover {
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
        .membership-form {
            display: flex;
            align-items: center;
        }
        .membership-select {
            width: 120px;
            padding: 0.25rem;
            border-radius: 4px;
            border: none;
            color: white;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        .membership-yearly {
            background: #28a745;
        }
        .membership-half-yearly {
            background: #007bff;
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
        .totals-box {
            background: #f8f9fa;
            border: 1px solid #6e8efb;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: center;
        }
        .totals-box p {
            margin: 0.5rem 0;
            font-weight: 600;
            color: #333;
        }
        .add-member-box {
            background: #f8f9fa;
            border: 1px solid #6e8efb;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .add-member-box input, .add-member-box select {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ced4da;
            margin-right: 0.5rem;
        }
        .add-member-box select {
            color: white;
        }
        .btn-add {
            padding: 0.5rem 1rem;
            background: #28a745;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add:hover {
            background: #218838;
            transform: scale(1.05);
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
    <div class="membership-container">
        <h3>Club Membership</h3>

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
                <div class="membership-box">
                    <div class="member-info">
                        <p class="info-text">Member ID: <?php echo htmlspecialchars($record['Member_id']); ?></p>
                        <p class="info-text">Club ID: <?php echo htmlspecialchars($record['club_id']); ?></p>
                        <p class="info-text">Membership Amount: <?php echo htmlspecialchars($record['member_fee']); ?></p>
                    </div>
                    <form class="membership-form" method="post" action="club_membership.php">
                        <select name="membership_type" class="membership-select 
                            <?php echo $record['Member_type'] === 'Yearly' ? 'membership-yearly' : 'membership-half-yearly'; ?>">
                            <option value="Yearly" <?php echo $record['Member_type'] === 'Yearly' ? 'selected' : ''; ?>>Yearly</option>
                            <option value="Half Yearly" <?php echo $record['Member_type'] === 'Half Yearly' ? 'selected' : ''; ?>>Half Yearly</option>
                        </select>
                        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($record['Member_id']); ?>">
                        <button type="submit" name="update_membership" class="btn-update">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="add-member-box">
            <form method="post" action="club_membership.php" class="d-flex align-items-center">
                <input type="text" name="sl" placeholder="Enter Member ID" required>
                <select name="new_membership_type" class="membership-select membership-yearly">
                    <option value="Yearly">Yearly</option>
                    <option value="Half Yearly">Half Yearly</option>
                </select>
                <select name="membership_amount" class="membership-select membership-yearly">
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                </select>
                <button type="submit" name="add_member" class="btn-add">Add Member</button>
            </form>
        </div>

        <div class="totals-box">
            <p>Total Members: <?php echo $total_members; ?></p>
            <p>Total Money: <?php echo $total_money; ?></p>
        </div>

        <a href="member_record.php" class="btn btn-primary">View Member Records</a>
        <a href="menu.php" class="btn btn-primary">Back to Club Lead Menu</a>
        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>