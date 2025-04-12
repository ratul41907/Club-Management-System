<?php
session_start();


if (!isset($_SESSION['member_logged_in']) || $_SESSION['member_logged_in'] !== true || !isset($_SESSION['member_id'])) {
    header("Location: general.php");
    exit();
}


if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}


if (isset($_POST['add_task'])) {
    $task_name = trim($_POST['task_name'] ?? '');
    $sl = trim($_POST['sl'] ?? '');
    $status = $_POST['status'] ?? 'pending';

    
    if (!empty($task_name) && !empty($sl) && is_numeric($sl)) {
        $_SESSION['tasks'][] = [
            'task_name' => $task_name,
            'sl' => $sl,
            'status' => $status,
            'member_id' => $_SESSION['member_id']
        ];
        $success_message = "Task added successfully!";
    } else {
        $error_message = "Please provide a valid task name and serial number.";
    }
}


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
    <title>Task Options - <?php echo htmlspecialchars($selected_club['club_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background: #e6f0fa;
            min-height: 100vh;
            padding: 2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .task-container {
            max-width: 600px;
            margin: auto;
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
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus, .form-select:focus {
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
        .task-list {
            margin-top: 2rem;
        }
        .task-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .club-info {
            text-align: center;
            margin-bottom: 1rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="task-container">
        <h3>Task Options</h3>
        <div class="club-info">
            Club: <?php echo htmlspecialchars($selected_club['club_name']); ?>
        </div>

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

        <form action="task_options.php" method="post">
            <div class="mb-3">
                <label for="task_name" class="form-label">Task Name</label>
                <input type="text" class="form-control" id="task_name" name="task_name" required>
            </div>
            <div class="mb-3">
                <label for="sl" class="form-label">Serial Number (SL)</label>
                <input type="number" class="form-control" id="sl" name="sl" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" name="add_task">Add Task</button>
        </form>

        <div class="task-list">
            <h4 class="mt-4">Your Tasks</h4>
            <?php if (empty($_SESSION['tasks'])): ?>
                <p>No tasks added yet.</p>
            <?php else: ?>
                <?php foreach ($_SESSION['tasks'] as $task): ?>
                    <?php if ($task['member_id'] === $_SESSION['member_id']): ?>
                        <div class="task-item">
                            <strong>SL: <?php echo htmlspecialchars($task['sl']); ?></strong><br>
                            Task: <?php echo htmlspecialchars($task['task_name']); ?><br>
                            Status: <?php echo htmlspecialchars(ucfirst($task['status'])); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>