<?php
session_start();

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// File to store members data
$membersFile = 'members.json';

// Load members from file if it exists, otherwise initialize with default data
if (file_exists($membersFile)) {
    $members = json_decode(file_get_contents($membersFile), true);
} else {
    $members = [
        [
            'first_name' => 'Karim',
            'middle_name' => 'Rahim',
            'last_name' => 'Sami',
            'dob' => '1998-05-15',
            'club_id' => '1',
            'mobile' => ['+8801712345678', '+8801712345679'] // Multiple numbers
        ],
        [
            'first_name' => 'Siam',
            'middle_name' => 'Ahmed',
            'last_name' => 'Siam',
            'dob' => '2000-09-22',
            'club_id' => '2',
            'mobile' => ['+8801812345678']
        ],
        [
            'first_name' => 'Sakib',
            'middle_name' => 'al',
            'last_name' => 'Hasan',
            'dob' => '1997-12-03',
            'club_id' => '3',
            'mobile' => ['+8801912345678', '+8801912345679', '+8801912345680']
        ],
        [
            'first_name' => 'Md',
            'middle_name' => 'SK',
            'last_name' => 'Rasel',
            'dob' => '1999-03-10',
            'club_id' => '1',
            'mobile' => ['+8801512345678']
        ],
        [
            'first_name' => 'Md',
            'middle_name' => 'Abdur',
            'last_name' => 'Rahim',
            'dob' => '2001-07-28',
            'club_id' => '2',
            'mobile' => ['+8801612345678', '+8801612345679']
        ]
    ];
    // Save initial data to file
    file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT));
}

// Use session to store temporary new members
if (!isset($_SESSION['temp_members'])) {
    $_SESSION['temp_members'] = [];
}

// Function to calculate age from DOB
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

// Handle form submission for adding new member to temporary list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recruit'])) {
    $club_id = $_POST['club_id'];
    $mobiles = array_filter(explode(',', $_POST['mobile'])); // Split by comma and remove empty entries
    $valid_mobiles = [];
    foreach ($mobiles as $mobile) {
        $mobile = trim($mobile);
        if (preg_match('/^\+880\d{10}$/', $mobile)) {
            $valid_mobiles[] = $mobile;
        }
    }
    if (in_array($club_id, ['1', '2', '3']) && !empty($valid_mobiles)) {
        $new_member = [
            'first_name' => $_POST['first_name'],
            'middle_name' => $_POST['middle_name'],
            'last_name' => $_POST['last_name'],
            'dob' => $_POST['dob'],
            'club_id' => $club_id,
            'mobile' => $valid_mobiles // Array of valid mobile numbers
        ];
        $_SESSION['temp_members'][] = $new_member; // Add to temporary session storage
    } else {
        $error = "Club ID must be 1, 2, or 3, and mobile numbers must be in format +880 followed by 10 digits, separated by commas.";
    }
}

// Handle save action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if (!empty($_SESSION['temp_members'])) {
        $members = array_merge($members, $_SESSION['temp_members']); // Merge temp members with permanent list
        file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT)); // Save to file
        $_SESSION['temp_members'] = []; // Clear temporary members
        $success = "New members saved successfully!";
    }
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Combine permanent and temporary members for display
$display_members = array_merge($members, $_SESSION['temp_members']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - Student Club Management System</title>
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
        .menu-container {
            max-width: 900px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .menu-container:hover {
            transform: translateY(-5px);
        }
        h3 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: #6e8efb;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0.5rem 0;
        }
        .btn-primary:hover {
            background: #5a75d9;
            transform: scale(1.05);
        }
        .btn-success {
            background: #28a745;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0.5rem 0;
        }
        .btn-success:hover {
            background: #218838;
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
        table {
            width: 100%;
            margin-top: 1rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
        }
        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
            th, td {
                padding: 0.5rem;
            }
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
                        clock.style.color = "#6e8efb"; // Morning
                    } else if (hour >= 12 && hour < 18) {
                        clock.style.color = "#ff6f61"; // Afternoon
                    } else {
                        clock.style.color = "#a777e3"; // Evening/Night
                    }
                }
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
</head>
<body>
    <div class="menu-container">
        <h3>Recruitment</h3>

        <!-- Members Table without Status -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                        <th>Club ID</th>
                        <th>Mobile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sl = 1; foreach ($display_members as $member): ?>
                        <tr>
                            <td><?php echo $sl++; ?></td>
                            <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['middle_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['dob']); ?></td>
                            <td><?php echo calculateAge($member['dob']); ?></td>
                            <td><?php echo htmlspecialchars($member['club_id']); ?></td>
                            <td><?php echo htmlspecialchars(implode(', ', $member['mobile'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recruitment Form with Multiple Mobile Numbers -->
        <h4 class="mt-4">Add New Member</h4>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" class="mt-3">
            <div class="mb-3">
                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
            </div>
            <div class="mb-3">
                <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" required>
            </div>
            <div class="mb-3">
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
            </div>
            <div class="mb-3">
                <input type="date" name="dob" class="form-control" required>
            </div>
            <div class="mb-3">
                <input type="text" name="mobile" class="form-control" placeholder="Mobile (e.g., +8801234567890, +8801234567891)" required title="Enter mobile numbers in format: +880 followed by 10 digits, separated by commas">
            </div>
            <div class="mb-3">
                <select name="club_id" class="form-control" required>
                    <option value="" disabled selected>Select Club ID</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>
            <button type="submit" name="recruit" class="btn btn-primary">Recruit Member</button>
        </form>

        <!-- Save Button Form -->
        <form method="POST" class="mt-3">
            <button type="submit" name="save" class="btn btn-success">Save All New Members</button>
        </form>

        <a href="?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>