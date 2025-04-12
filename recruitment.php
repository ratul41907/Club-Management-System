<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}


$membersFile = 'members.json';


if (file_exists($membersFile)) {
    $members = json_decode(file_get_contents($membersFile), true);
    // Normalize mobile numbers to arrays
    foreach ($members as &$member) {
        if (!is_array($member['mobile'])) {
            $member['mobile'] = [$member['mobile']];
        }
    }
    unset($member); 
} else {
    $members = [
        [
            'first_name' => 'Karim',
            'middle_name' => 'Rahim',
            'last_name' => 'Sami',
            'dob' => '1998-05-15',
            'club_id' => '1',
            'mobile' => ['+8801712345678', '+8801715938093']
        ],
        [
            'first_name' => 'Siam',
            'middle_name' => 'Ahmed',
            'last_name' => 'Siam',
            'dob' => '2000-09-22',
            'club_id' => '2',
            'mobile' => ['+8801812345678']
        ]
    ];
    
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

// Handle form submission for adding new member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['recruit']) || isset($_POST['save_direct']))) {
    $club_id = trim($_POST['club_id'] ?? '');
    $mobile_numbers = array_filter($_POST['mobile'] ?? [], function($mobile) {
        return !empty($mobile) && preg_match('/^\+880[0-9]{10}$/', trim($mobile));
    });

    $errors = [];
    if (!in_array($club_id, ['1', '2', '3'])) {
        $errors[] = "Club ID must be 1, 2, or 3.";
    }
    if (empty($mobile_numbers)) {
        $errors[] = "At least one valid mobile number is required in format +880 followed by 10 digits (e.g., +8801234567890).";
    }
    // Validate DOB format (YYYY-MM-DD)
    $dob = trim($_POST['dob'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) || !DateTime::createFromFormat('Y-m-d', $dob)) {
        $errors[] = "Invalid date of birth format. Use YYYY-MM-DD.";
    }

    if (empty($errors)) {
        $new_member = [
            'first_name' => trim($_POST['first_name']),
            'middle_name' => trim($_POST['middle_name']),
            'last_name' => trim($_POST['last_name']),
            'dob' => $dob,
            'club_id' => $club_id,
            'mobile' => array_values($mobile_numbers) // Store as array
        ];

        if (isset($_POST['save_direct'])) {
            // Save directly to members.json
            $members[] = $new_member;
            file_put_contents($membersFile, json_encode($members, JSON_PRETTY_PRINT));
            $success = "Member saved successfully!";
        } else {
            // Add to temporary session storage
            $_SESSION['temp_members'][] = $new_member;
            $success = "Member added to temporary list!";
        }
    } else {
        $error = implode(' ', $errors);
    }
}

// Handle bulk save action
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
            margin: 0.5rem;
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
            margin: 0.5rem;
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
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
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
            // Clock update
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

            // Mobile number validation
            const mobileInputs = document.querySelectorAll('input[name="mobile[]"]');
            mobileInputs.forEach(input => {
                input.addEventListener('input', function () {
                    const value = this.value.trim();
                    const regex = /^\+880[0-9]{10}$/;
                    if (value === '' || regex.test(value)) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
            });

            // DOB validation
            const dobInput = document.getElementById('dob');
            dobInput.addEventListener('input', function () {
                const value = this.value;
                const regex = /^\d{4}-\d{2}-\d{2}$/;
                if (regex.test(value) && Date.parse(value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    </script>
</head>
<body>
    <div class="menu-container">
        <h3>Recruitment</h3>

        <!-- Members Table with SL and Mobile -->
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
                    <?php $sl = 1; foreach ($display_members as $index => $member): ?>
                        <tr>
                            <td><?php echo $sl++; ?></td>
                            <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['middle_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['dob']); ?></td>
                            <td><?php echo calculateAge($member['dob']); ?></td>
                            <td><?php echo htmlspecialchars($member['club_id']); ?></td>
                            <td>
                                <?php
                                if (is_array($member['mobile'])) {
                                    echo htmlspecialchars(implode(', ', $member['mobile']));
                                } else {
                                    echo htmlspecialchars($member['mobile']);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recruitment Form with Multiple Mobile Numbers -->
        <h4 class="mt-4">Add New Member</h4>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" class="mt-3" novalidate>
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" required>
                <div class="invalid-feedback">Please enter a first name.</div>
            </div>
            <div class="mb-3">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" class="form-control" placeholder="Middle Name" required>
                <div class="invalid-feedback">Please enter a middle name.</div>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" required>
                <div class="invalid-feedback">Please enter a last name.</div>
            </div>
            <div class="mb-3">
                <label for=" Ascendantly for DOB
                <input type="date" name="dob" id="dob" class="form-control" placeholder="Select Date of Birth" required>
                <div class="invalid-feedback">Please enter a valid date (YYYY-MM-DD).</div>
            </div>
            <div class="mb-3">
                <label for="mobile1" class="form-label">Mobile Number 1 (Required)</label>
                <input type="text" name="mobile[]" id="mobile1" class="form-control" placeholder="e.g., +8801234567890" required>
                <div class="invalid-feedback">Please enter a valid mobile number (e.g., +8801234567890).</div>
            </div>
            <div class="mb-3">
                <label for="mobile2" class="form-label">Mobile Number 2 (Optional)</label>
                <input type="text" name="mobile[]" id="mobile2" class="form-control" placeholder="e.g., +8801234567890">
                <div class="invalid-feedback">Please enter a valid mobile number (e.g., +8801234567890).</div>
            </div>
            <div class="mb-3">
                <label for="mobile3" class="form-label">Mobile Number 3 (Optional)</label>
                <input type="text" name="mobile[]" id="mobile3" class="form-control" placeholder="e.g., +8801234567890">
                <div class="invalid-feedback">Please enter a valid mobile number (e.g., +8801234567890).</div>
            </div>
            <div class="mb-3">
                <label for="club_id" class="form-label">Club ID</label>
                <select name="club_id" id="club_id" class="form-control" required>
                    <option value="" disabled selected>Select Club ID</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
                <div class="invalid-feedback">Please select a club ID.</div>
            </div>
            <button type="submit" name="recruit" class="btn btn-primary" title="Add to temporary list for later saving">Recruit Member</button>
            <button type="submit" name="save_direct" class="btn btn-success" title="Save directly to permanent storage">Save Member</button>
        </form>

        <!-- Bulk Save Button Form -->
        <form method="POST" class="mt-3">
            <button type="submit" name="save" class="btn btn-success">Save All New Members</button>
        </form>

        <a href="?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>