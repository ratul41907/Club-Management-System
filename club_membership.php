<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: menu.php"); // Redirect back to menu.php if not a lead
    exit();
}


if (!isset($_SESSION['member_records']) || !is_array($_SESSION['member_records'])) {
    $_SESSION['member_records'] = [];
}


if (isset($_POST['update_membership'])) {
    $index = filter_input(INPUT_POST, 'record_index', FILTER_VALIDATE_INT);
    $new_membership_type = filter_input(INPUT_POST, 'membership_type', FILTER_SANITIZE_STRING);
    
    if ($index !== false && $index !== null && isset($_SESSION['member_records'][$index]) && 
        in_array($new_membership_type, ['Yearly', 'Half Yearly'])) {
        $_SESSION['member_records'][$index]['membership_type'] = $new_membership_type;
        $_SESSION['member_records'][$index]['membership_amount'] = ($new_membership_type === 'Yearly') ? 1000 : 500;
    }
    header("Location: club_membership.php"); // Refresh page
    exit();
}


if (isset($_POST['add_member'])) {
    $sl = filter_input(INPUT_POST, 'sl', FILTER_SANITIZE_STRING);
    $membership_type = filter_input(INPUT_POST, 'new_membership_type', FILTER_SANITIZE_STRING);
    $membership_amount = filter_input(INPUT_POST, 'membership_amount', FILTER_VALIDATE_INT);

    if (!empty($sl) && in_array($membership_type, ['Yearly', 'Half Yearly']) && 
        in_array($membership_amount, [500, 1000])) {
        $_SESSION['member_records'][] = [
            'record_id' => $sl, // Using SL as record_id
            'club_id' => $_SESSION['selected_club_id'] ?? 'N/A', // From dashboard.php
            'membership_type' => $membership_type,
            'membership_amount' => $membership_amount
        ];
    }
    header("Location: club_membership.php"); // Refresh page
    exit();
}


foreach ($_SESSION['member_records'] as &$record) {
    if (!isset($record['membership_type']) || !isset($record['membership_amount'])) {
        $record['membership_type'] = 'Yearly';
        $record['membership_amount'] = 1000;
    }
}
unset($record);

$member_records = $_SESSION['member_records'];


$total_members = count($member_records);
$total_money = array_sum(array_column($member_records, 'membership_amount'));
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
        
        <?php if (empty($member_records)): ?>
            <p class="text-center">No selected members found.</p>
        <?php else: ?>
            <?php foreach ($member_records as $index => $record): ?>
                <?php 
                $record_id = isset($record['record_id']) ? htmlspecialchars($record['record_id']) : 'N/A';
                $club_id = isset($record['club_id']) ? htmlspecialchars($record['club_id']) : 'N/A';
                $membership_amount = isset($record['membership_amount']) ? htmlspecialchars($record['membership_amount']) : 'N/A';
                $membership_type = isset($record['membership_type']) ? $record['membership_type'] : 'Yearly';
                ?>
                <div class="membership-box">
                    <div class="member-info">
                        <p class="info-text">Record ID: <?php echo $record_id; ?></p>
                        <p class="info-text">Club ID: <?php echo $club_id; ?></p>
                        <p class="info-text">Membership Amount: <?php echo $membership_amount; ?></p>
                    </div>
                    <form class="membership-form" method="post" action="club_membership.php">
                        <select name="membership_type" class="membership-select 
                            <?php echo $membership_type === 'Yearly' ? 'membership-yearly' : 'membership-half-yearly'; ?>">
                            <option value="Yearly" <?php echo $membership_type === 'Yearly' ? 'selected' : ''; ?>>Yearly</option>
                            <option value="Half Yearly" <?php echo $membership_type === 'Half Yearly' ? 'selected' : ''; ?>>Half Yearly</option>
                        </select>
                        <input type="hidden" name="record_index" value="<?php echo $index; ?>">
                        <button type="submit" name="update_membership" class="btn-update">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="add-member-box">
            <form method="post" action="club_membership.php" class="d-flex align-items-center">
                <input type="text" name="sl" placeholder="Enter SL" required>
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