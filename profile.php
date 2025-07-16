<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

// Get username and team info
$username = '';
$team_name = '';
$team_code = '';

if (isset($_SESSION['admin_logged_in'])) {
    // Admin profile
    $username = $_SESSION['admin_username'];
    $team_name = 'Admin';
    $team_code = 'N/A';
} elseif (isset($_SESSION['team_id'])) {
    // Team user profile
    $team_id = $_SESSION['team_id'];

    // Get current user's username
    $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_stmt->bind_result($username);
    $user_stmt->fetch();
    $user_stmt->close();

    // Get team info
    $team_stmt = $conn->prepare("SELECT team_name, team_code FROM teams WHERE id = ?");
    $team_stmt->bind_param("i", $team_id);
    $team_stmt->execute();
    $team_stmt->bind_result($team_name, $team_code);
    $team_stmt->fetch();
    $team_stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - Duothan 5.0</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1b0f17; color: #fff; }
        .profile-container {
            background: #2d1b2f;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 77, 77, 0.3);
            width: 100%;
            max-width: 400px;
            margin: 60px auto;
            text-align: center;
        }
        h2 { color: #ff4d4d; }
        .profile-info { margin: 30px 0; }
        .profile-info label { color: #ffc0cb; font-weight: bold; }
        .profile-info div { margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Profile</h2>
        <div class="profile-info">
            <div>
                <label>Username:</label> <?php echo htmlspecialchars($username); ?>
            </div>
            <div>
                <label>Team Name:</label> <?php echo htmlspecialchars($team_name); ?>
            </div>
            <div>
                <label>Team Code:</label> <?php echo htmlspecialchars($team_code); ?>
            </div>
        </div>
        <a href="home.php" style="color:#ff4d4d;">&larr; Back to Home</a>
    </div>
</body>