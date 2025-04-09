<?php
session_start();

// Check if user is logged in (either as lead or general member)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Determine user role
$is_lead = isset($_SESSION['lead_logged_in']) && $_SESSION['lead_logged_in'] === true;
$is_general = isset($_SESSION['general_logged_in']) && $_SESSION['general_logged_in'] === true;

if (!$is_lead && !$is_general) {
    header("Location: index.php");
    exit();
}

// Ensure events array exists in session
if (!isset($_SESSION['events']) || !is_array($_SESSION['events'])) {
    $_SESSION['events'] = [
        ['event_id' => 1, 'event_name' => 'Skii Adventure', 'event_date' => '2025-06-12'],
        ['event_id' => 2, 'event_name' => 'MS Word Basic Skill', 'event_date' => '2025-07-19'],
        ['event_id' => 3, 'event_name' => 'Typorgraphiy', 'event_date' => '2025-08-03'],
        ['event_id' => 4, 'event_name' => 'Basic Video Editing', 'event_date' => '2025-09-15'],
        ['event_id' => 5, 'event_name' => 'Higher Studies', 'event_date' => '2025-10-27']
    ];
}

// Handle adding a new event (only for leads)
if (isset($_POST['add_event'])) {
    if (!$is_lead) {
        $_SESSION['access_error'] = "You do not have permission to add events!";
        header("Location: club_events.php");
        exit();
    }
    
    $event_name = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    
    if (!empty($event_name) && !empty($event_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        $existing_ids = array_column($_SESSION['events'], 'event_id');
        $new_event_id = empty($existing_ids) ? 1 : (max($existing_ids) + 1);
        
        $_SESSION['events'][] = [
            'event_id' => $new_event_id,
            'event_name' => $event_name,
            'event_date' => $event_date
        ];
    }
    header("Location: club_events.php");
    exit();
}

// Handle deleting an event (only for leads)
if (isset($_POST['delete_event'])) {
    if (!$is_lead) {
        $_SESSION['access_error'] = "You do not have permission to delete events!";
        header("Location: club_events.php");
        exit();
    }
    
    $index = filter_input(INPUT_POST, 'event_index', FILTER_VALIDATE_INT);
    
    if ($index !== false && $index !== null && isset($_SESSION['events'][$index])) {
        unset($_SESSION['events'][$index]);
        $_SESSION['events'] = array_values($_SESSION['events']);
    }
    header("Location: club_events.php");
    exit();
}

$events = $_SESSION['events'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Events - Student Club Management System</title>
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
        .events-container {
            max-width: 900px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .events-container:hover {
            transform: translateY(-5px);
        }
        h3 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        .event-box {
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
        .event-box:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(110, 142, 251, 0.2);
        }
        .event-info {
            flex-grow: 1;
        }
        .info-text {
            margin: 0.25rem 0;
            color: #333;
        }
        .btn-delete {
            padding: 0.25rem 0.75rem;
            background: #dc3545;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        .add-event-form {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .form-control {
            max-width: 200px;
        }
        .btn-add {
            padding: 0.25rem 0.75rem;
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
    <div class="events-container">
        <h3>Club Events</h3>

        <!-- Display access error if any -->
        <?php if (isset($_SESSION['access_error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['access_error']); unset($_SESSION['access_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add Event Form (visible and functional only for leads) -->
        <?php if ($is_lead): ?>
            <form class="add-event-form" method="post" action="club_events.php">
                <input type="text" name="event_name" class="form-control" placeholder="Event Name" required>
                <input type="date" name="event_date" class="form-control" required>
                <button type="submit" name="add_event" class="btn-add">Add Event</button>
            </form>
        <?php endif; ?>

        <!-- Event List -->
        <?php if (empty($events)): ?>
            <p class="text-center">No events found.</p>
        <?php else: ?>
            <?php foreach ($events as $index => $event): ?>
                <div class="event-box">
                    <div class="event-info">
                        <p class="info-text">Event ID: <?php echo htmlspecialchars($event['event_id']); ?></p>
                        <p class="info-text">Event Name: <?php echo htmlspecialchars($event['event_name']); ?></p>
                        <p class="info-text">Event Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
                    </div>
                    <?php if ($is_lead): ?>
                        <form method="post" action="club_events.php">
                            <input type="hidden" name="event_index" value="<?php echo $index; ?>">
                            <button type="submit" name="delete_event" class="btn-delete">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($is_lead): ?>
            <a href="club_membership.php" class="btn btn-primary">View Club Membership</a>
            <a href="clubregistration.php" class="btn btn-primary">Back to Club Registration</a>
        <?php endif; ?>
        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>