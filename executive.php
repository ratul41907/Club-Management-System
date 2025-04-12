<?php
session_start();

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// File to store executives data
$executivesFile = 'executives.json';

// Load executives from file if it exists, otherwise initialize with default data
if (file_exists($executivesFile)) {
    $executives = json_decode(file_get_contents($executivesFile), true);
    if (!is_array($executives)) {
        $executives = []; // Handle corrupted file
    }
} else {
    $executives = [
        [
            "position" => "Chair",
            "name" => "Fahim",
            "start_date" => "2025-01-01",
            "end_date" => "2025-12-31"
        ],
        [
            "position" => "Vice Chair",
            "name" => "Omar",
            "start_date" => "2025-01-01",
            "end_date" => "2025-12-31"
        ],
        [
            "position" => "Secretary",
            "name" => "Sadman",
            "start_date" => "2025-01-01",
            "end_date" => "2025-12-31"
        ],
        [
            "position" => "Treasurer",
            "name" => "Salim",
            "start_date" => "2025-01-01",
            "end_date" => "2025-12-31"
        ],
        [
            "position" => "Webmaster",
            "name" => "Asif",
            "start_date" => "2025-01-01",
            "end_date" => "2025-12-31"
        ]
    ];
    // Save initial data to file
    file_put_contents($executivesFile, json_encode($executives, JSON_PRETTY_PRINT));
}

// Handle form submission for adding new executive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_executive'])) {
    $position = trim($_POST['position'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    $errors = [];
    if (empty($position)) {
        $errors[] = "Position is required.";
    }
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !DateTime::createFromFormat('Y-m-d', $start_date)) {
        $errors[] = "Invalid start date format. Use YYYY-MM-DD.";
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        $errors[] = "Invalid end date format. Use YYYY-MM-DD.";
    }

    if (empty($errors)) {
        $new_executive = [
            'position' => $position,
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        $executives[] = $new_executive;
        file_put_contents($executivesFile, json_encode($executives, JSON_PRETTY_PRINT));
        $success = "Executive added successfully!";
    } else {
        $error = implode(' ', $errors);
    }
}

// Handle removal of an executive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_executive'])) {
    $index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);
    if ($index !== false && isset($executives[$index])) {
        array_splice($executives, $index, 1);
        file_put_contents($executivesFile, json_encode($executives, JSON_PRETTY_PRINT));
        $success = "Executive removed successfully!";
    } else {
        $error = "Invalid executive selected for removal.";
    }
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Committee - Student Club Management System</title>
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
        .executive-container {
            max-width: 800px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .executive-container:hover {
            transform: translateY(-5px);
        }
        h3, h4 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #6e8efb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        .info-box:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(110, 142, 251, 0.2);
        }
        .position-box {
            background: #6e8efb;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px 0 0 8px;
            width: 150px;
            text-align: center;
            font-weight: 600;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .details-box {
            flex-grow: 1;
        }
        .info-text {
            margin: 0.25rem 0;
            color: #333;
        }
        .btn-danger {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1.5rem;
        }
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-remove:hover {
            background: #c82333;
            transform: scale(1.05);
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
        #clock {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 1rem;
            transition: color 1s ease;
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
            .position-box {
                width: 120px;
                font-size: 0.9rem;
            }
            .info-box {
                flex-direction: column;
                align-items: flex-start;
            }
            .position-box {
                border-radius: 8px 8px 0 0;
                margin-right: 0;
                margin-bottom: 0.5rem;
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

            // Form validation
            const inputs = document.querySelectorAll('#add-executive-form input');
            inputs.forEach(input => {
                input.addEventListener('input', function () {
                    if (this.type === 'date') {
                        const value = this.value;
                        const regex = /^\d{4}-\d{2}-\d{2}$/;
                        if (value === '' || (regex.test(value) && Date.parse(value))) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        }
                    } else {
                        if (this.value.trim() === '') {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        }
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="executive-container">
        <h3>Executive Committee</h3>
        
        <!-- Success/Error Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Executive List -->
        <?php foreach ($executives as $index => $exec): ?>
            <div class="info-box">
                <div class="position-box">
                    <?php echo htmlspecialchars($exec['position']); ?>
                </div>
                <div class="details-box">
                    <p class="info-text">Name: <?php echo htmlspecialchars($exec['name']); ?></p>
                    <p class="info-text">Term Start: <?php echo htmlspecialchars($exec['start_date']); ?></p>
                    <p class="info-text">Term End: <?php echo htmlspecialchars($exec['end_date']); ?></p>
                </div>
                <form method="POST" style="margin-left: 1rem;">
                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                    <button type="submit" name="remove_executive" class="btn-remove" onclick="return confirm('Are you sure you want to remove this executive?');">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>

        <!-- Add Executive Form -->
        <h4 class="mt-4">Add New Executive</h4>
        <form id="add-executive-form" method="POST" class="mt-3" novalidate>
            <div class="mb-3">
                <label for="position" class="form-label">Position</label>
                <input type="text" name="position" id="position" class="form-control" placeholder="e.g., Chair" required>
                <div class="invalid-feedback">Please enter a position.</div>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="e.g., John Doe" required>
                <div class="invalid-feedback">Please enter a name.</div>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Term Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid date (YYYY-MM-DD).</div>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">Term End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid date (YYYY-MM-DD).</div>
            </div>
            <button type="submit" name="add_executive" class="btn btn-primary">Add Executive</button>
        </form>

        <a href="?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>