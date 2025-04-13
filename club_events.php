<?php
session_start();


require_once 'db_connect.php';


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['lead_logged_in']) || $_SESSION['lead_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}


if (isset($_POST['add_event'])) {
    $event_name = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
    
    if (!empty($event_name) && !empty($event_date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        // Insert event into database
        $stmt = $conn->prepare("INSERT INTO club_events (event_name, event_date) VALUES (?, ?)");
        $stmt->bind_param("ss", $event_name, $event_date);
        
        if ($stmt->execute()) {
            $success_message = "Event added successfully!";
        } else {
            $error_message = "Error adding event: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please provide a valid event name and date.";
    }
}

// Handle event deletion
if (isset($_POST['delete_event'])) {
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    
    if ($event_id !== false && $event_id !== null) {
        // Delete event from database
        $stmt = $conn->prepare("DELETE FROM club_events WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        
        if ($stmt->execute()) {
            $success_message = "Event deleted successfully!";
        } else {
            $error_message = "Error deleting event: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Invalid event ID.";
    }
}

// Fetch all events from database
$result = $conn->query("SELECT event_id, event_name, event_date FROM club_events ORDER BY event_date ASC");
$events = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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

        <form class="add-event-form" method="post" action="club_events.php">
            <input type="text" name="event_name" class="form-control" placeholder="Event Name" required>
            <input type="date" name="event_date" class="form-control" required>
            <button type="submit" name="add_event" class="btn-add">Add Event</button>
        </form>

        <?php if (empty($events)): ?>
            <p class="text-center">No events found.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="event-box">
                    <div class="event-info">
                        <p class="info-text">Event ID: <?php echo htmlspecialchars($event['event_id']); ?></p>
                        <p class="info-text">Event Name: <?php echo htmlspecialchars($event['event_name']); ?></p>
                        <p class="info-text">Event Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
                    </div>
                    <form method="post" action="club_events.php">
                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                        <button type="submit" name="delete_event" class="btn-delete">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="dashboard.php?logout=true" class="btn btn-danger">Logout</a>
        <div id="clock"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>