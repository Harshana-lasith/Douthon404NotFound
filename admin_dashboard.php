
<?php
session_start();
include 'db.php';

// Optional: Only allow admins
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
// Get Registered Teams count
$teams_count = 0;
$result = $conn->query("SELECT COUNT(*) AS count FROM teams");
if ($row = $result->fetch_assoc()) {
    $teams_count = $row['count'];
}

// Get Active Challenges count (assuming a 'challenges' table with 'active' column)
$challenges_count = 0;
$result = $conn->query("SELECT COUNT(*) AS count FROM challenges WHERE active = 1");
if ($row = $result->fetch_assoc()) {
    $challenges_count = $row['count'];
// Get Total Submissions count (assuming a 'submissions' table)
$submissions_count = 0;
$result = $conn->query("SELECT COUNT(*) AS count FROM submissions");
if ($row = $result->fetch_assoc()) {
    $submissions_count = $row['count'];
}}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard | Duothan 5.0</title>
  <style>
    body {
      background: #121212;
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: radial-gradient(circle, rgba(0, 0, 0, 1) 0%, rgba(0, 30, 60, 1) 100%);
    }

    .dashboard-container {
      background: rgba(0, 0, 50, 0.9);
      width: 100%;
      max-width: 1300px;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 50px rgba(0, 255, 255, 0.5);
      text-align: center;
    }

    h1 {
      font-size: 38px;
      color: #00ffcc;
      margin-bottom: 40px;
    }

    .stats-container {
      display: flex;
      justify-content: space-evenly;
      gap: 40px;
      margin-bottom: 40px;
    }

    .stat-box {
      background: #1a1a2f;
      border-radius: 12px;
      padding: 30px;
      width: 250px;
      box-shadow: 0 5px 25px rgba(0, 255, 255, 0.3);
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-box:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 40px rgba(0, 255, 255, 0.5);
    }

    .stat-box h3 {
      font-size: 24px;
      color: #00ffcc;
      margin-bottom: 15px;
    }

    .stat-box p {
      font-size: 20px;
      color: #ffffff;
    }

    .action-buttons {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-bottom: 40px;
    }

    .action-buttons .btn {
      background: #ff4d4d;
      color: white;
      padding: 16px 32px;
      font-size: 20px;
      border-radius: 8px;
      cursor: pointer;
      width: 250px;
      transition: background-color 0.3s, transform 0.3s;
      box-shadow: 0 8px 15px rgba(255, 77, 77, 0.4);
    }

    .action-buttons .btn:hover {
      background-color: #cc2929;
      transform: scale(1.05);
      box-shadow: 0 8px 35px rgba(255, 77, 77, 0.6);
    }

    .footer {
      font-size: 14px;
      color: #bbb;
      margin-top: 40px;
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
      .stats-container {
        flex-direction: column;
        gap: 20px;
      }

      .stat-box {
        width: 100%;
      }

      .action-buttons {
        flex-direction: column;
      }

      .action-buttons .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <h1>Admin Dashboard - Duothan 5.0</h1>

    <!-- Stats Section -->
    <div class="stats-container">
      <div class="stat-box">
        <h3>Registered Teams</h3>
        <p><?php echo $teams_count; ?></p>
      </div>
      <div class="stat-box">
        <h3>Active Challenges</h3>
        <p><?php echo $challenges_count; ?></p>
      </div>
      <div class="stat-box">
        <h3>Total Submissions</h3>
        <p><?php echo $submissions_count; ?></p>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <div class="btn">Manage Challenges</div>
      <div class="btn">View Leaderboard</div>
      <div class="btn">Team Submissions</div>
    </div>

    <div class="footer">
      © 2025 Duothan Hackathon - Admin Portal
    </div>
  </div>
</body>
</html>>