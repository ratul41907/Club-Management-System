<?php
session_start();

// Check if user is logged in as a lead
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Executive data
$executives = [
    [
        "position" => "Chair",
        "name" => "Fahim",
        "start_date" => "1st Jan 2025",
        "end_date" => "31st Dec 2025"
    ],
    [
        "position" => "Vice Chair",
        "name" => "Omar",
        "start_date" => "1st Jan 2025",
        "end_date" => "31st Dec 2025"
    ],
    [
        "position" => "Secretary",
        "name" => "Sadman",
        "start_date" => "1st Jan 2025",
        "end_date" => "31st Dec 2025"
    ],
    [
        "position" => "Treasurer",
        "name" => "Salim",
        "start_date" => "1st Jan 2025",
        "end_date" => "31st Dec 2025"
    ],
    [
        "position" => "Webmaster",
        "name" => "Asif",
        "start_date" => "1st Jan 2025",
        "end_date" => "31st Dec 2025"
    ]
];
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
        h3 {
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
    <div class="executive-container">
        <h3>Executive Committee</h3>
        
        <?php foreach ($executives as $exec): ?>
            <div class="info-box">
                <div class="position-box">
                    <?php echo htmlspecialchars($exec['position']); ?>
                </div>
                <div class="details-box">
                    <p class="info-text">Name: <?php echo htmlspecialchars($exec['name']); ?></p>
                    <p class="info-text">Term Start: <?php echo htmlspecialchars($exec['start_date']); ?></p>
                    <p class="info-text">Term End: <?php echo htmlspecialchars($exec['end_date']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>

        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>