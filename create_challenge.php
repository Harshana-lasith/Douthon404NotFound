<?php
session_start();
include 'db.php';

// Only allow admins
if (isset($_SESSION['admin_logged_in'])) {
    $active = isset($_POST['active']) ? 1 : 0; // Admin can set active
} else {
    $active = 0; // User submissions are always inactive (pending approval)
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $flag = trim($_POST['flag']);
    $points = intval($_POST['points']);
    $active = isset($_POST['active']) ? 1 : 0;

    if ($title && $description && $flag && $points > 0) {
        $stmt = $conn->prepare("INSERT INTO challenges (title, description, flag, points, active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $title, $description, $flag, $points, $active);
        if ($stmt->execute()) {
            $message = "Challenge created successfully!";
        } else {
            $message = "Error creating challenge.";
        }
        $stmt->close();
    } else {
        $message = "All fields are required and points must be greater than 0.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Challenge</title>
    <style>
        body { background: #1b0f17; color: #fff; font-family: Arial, sans-serif; }
        .container {
            background: #2d1b2f;
            padding: 40px;
            border-radius: 10px;
            max-width: 500px;
            margin: 60px auto;
            box-shadow: 0 0 20px rgba(255, 77, 77, 0.2);
        }
        h2 { color: #00cc99; }
        label { display: block; margin-top: 18px; }
        input[type="text"], textarea, input[type="number"] {
            width: 100%; padding: 10px; border-radius: 5px; border: none;
            margin-top: 6px; background: #3a2d3c; color: #fff; font-size: 16px;
        }
        button {
            margin-top: 24px; background: #00cc99; color: #fff; border: none;
            padding: 12px 28px; border-radius: 6px; font-weight: bold; cursor: pointer;
            font-size: 16px; transition: background 0.2s;
        }
        button:hover { background: #009977; }
        .msg { margin-top: 18px; color: #ff4d4d; }
        a { color: #00cc99; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Challenge</h2>
        <?php if ($message): ?>
            <div class="msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Title</label>
            <input type="text" name="title" required>
            <label>Description</label>
            <textarea name="description" rows="5" required></textarea>
            <label>Flag</label>
            <input type="text" name="flag" required>
            <label>Points</label>
            <input type="number" name="points" min="1" required>
            <label>
                <input type="checkbox" name="active" checked> Active
            </label>
            <button type="submit">Create Challenge</button>
        </form>
        <div style="margin-top:20px;">
            <a href="admin_dashboard.php">&larr; Back to Dashboard</a>
        </div>