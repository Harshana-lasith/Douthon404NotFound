<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

// Handle create team
if (isset($_POST['create_team'])) {
    $team_name = $_POST['team_name'];
    $user_id = $_SESSION['user_id']; // Always use the logged-in user's ID
    $team_code = substr(md5(uniqid(rand(), true)), 0, 8);

    $stmt = $conn->prepare("SELECT id FROM teams WHERE team_name = ?");
    $stmt->bind_param("s", $team_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Team name already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO teams (team_name, team_code, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $team_name, $team_code, $user_id);
        $stmt->execute();
        $message = "Team created! Your team code: $team_code";
        $_SESSION['team_id'] = $conn->insert_id;
        $_SESSION['team_name'] = $team_name;
    }
}

// Handle join team
if (isset($_POST['join_team'])) {
    $team_code = $_POST['team_code'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, team_name FROM teams WHERE team_code = ?");
    $stmt->bind_param("s", $team_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($team_id, $team_name);
        $stmt->fetch();

        // Update user's team_id
        $update = $conn->prepare("UPDATE users SET team_id = ? WHERE id = ?");
        $update->bind_param("ii", $team_id, $user_id);
        $update->execute();

        $_SESSION['team_id'] = $team_id;
        $_SESSION['team_name'] = $team_name;
        $message = "Joined team: $team_name";
    } else {
        $message = "Invalid team code!";
    }
}

$leaderboard = [];
$lb_query = "
    SELECT t.team_name, COALESCE(SUM(s.points),0) AS total_points
    FROM teams t
    LEFT JOIN submissions s ON t.id = s.team_id
    GROUP BY t.id
    ORDER BY total_points DESC
    LIMIT 10
";
$lb_result = $conn->query($lb_query);
if ($lb_result) {
    while ($row = $lb_result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}

// Fetch all active challenges
$challenges = [];
$result = $conn->query("SELECT id, title, description, points FROM challenges WHERE active = 1");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $challenges[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Duothan 5.0 – Round 1</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Global Styles */
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f4f7fb;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    h1 {
      font-size: 28px;
      font-weight: bold;
      color: #333;
      margin: 20px 0;
      padding: 0 10px;
    }

    a {
      text-decoration: none;
    }

    /* Profile Link */
    .profile-link {
      position: absolute;
      top: 20px;
      right: 30px;
      background: #3498db;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .profile-link:hover {
      background: #2980b9;
    }

    /* Button Styles */
    button {
      background: #3498db;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #2980b9;
    }

    /* Forms */
    input[type="text"] {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 16px;
      width: 300px;
    }

    /* Team Creation/Join Section */
    .team-forms {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
    }

    .team-forms form {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .team-forms input[type="text"] {
      width: 100%;
    }

    /* Container */
    .container {
      display: flex;
      justify-content: space-between;
      padding: 30px;
    }

    /* Challenges Section */
    .challenges {
      width: 70%;
    }

    .challenge-card {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .challenge-card h4 {
      color: #3498db;
      font-size: 24px;
    }

    .challenge-card p {
      font-size: 16px;
      color: #555;
    }

    .challenge-card strong {
      font-weight: bold;
      color: #2c3e50;
    }

    /* Sidebar */
    .sidebar {
      background: #2d3e50;
      padding: 30px 20px;
      border-radius: 10px;
      width: 300px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .sidebar h4 {
      color: #fff;
      font-size: 20px;
      margin-bottom: 15px;
    }

    .sidebar .rank {
      font-size: 18px;
      color: #fff;
      margin-bottom: 25px;
    }

    /* Leaderboard */
    .leaderboard-list {
      list-style: none;
      padding-left: 0;
      margin-bottom: 15px;
    }

    .leaderboard-list li {
      background: #34495e;
      color: white;
      font-size: 16px;
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: background 0.3s ease;
    }

    .leaderboard-list li:hover {
      background: #16a085;
    }

    .leaderboard-list li strong {
      color: #f1c40f;
      font-weight: bold;
    }

    /* Mobile Responsiveness */
    @media (max-width: 900px) {
      .container {
        flex-direction: column;
        align-items: center;
      }

      .challenges {
        width: 100%;
      }

      .sidebar {
        width: 100%;
        margin-top: 30px;
      }

      .team-forms {
        flex-direction: column;
        gap: 15px;
      }

      .profile-link {
        top: 15px;
        right: 20px;
      }
    }
  </style>
</head>
<body>

  <a href="profile.php" class="profile-link">Profile</a>

  <h1>Duothan 5.0 - Round 1 <a href="#" class="details">Details ▶️</a></h1>

  <?php if (isset($message)) echo "<p style='color:red;'>$message</p>"; ?>

  <!-- Team creation/join forms -->
  <div class="team-forms">
    <form method="POST">
      <input type="text" name="team_name" placeholder="Team Name" required>
      <button type="submit" name="create_team">Create Team</button>
    </form>
    <form method="POST">
      <input type="text" name="team_code" placeholder="Team Code" required>
      <button type="submit" name="join_team">Join Team</button>
    </form>
  </div>

  <a href="create_challenge.php" style="position: absolute; top: 20px; right: 170px; background: #00cc99; color: #fff; padding: 10px 22px; border-radius: 6px; font-weight: bold; transition: background 0.2s; z-index: 101;">+ Create Challenge</a>

  <div class="container">
    <!-- Left: Challenges -->
    <div class="challenges">
      <h3>Challenges</h3>
      <?php if (count($challenges) > 0): ?>
        <?php foreach ($challenges as $challenge): ?>
          <div class="challenge-card">
            <h4><?php echo htmlspecialchars($challenge['title']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($challenge['description'])); ?></p>
            <p><strong>Points:</strong> <?php echo (int)$challenge['points']; ?></p>
            <button class="solve-btn" onclick="window.location.href='challenge.php?id=<?php echo $challenge['id']; ?>'">Solve Challenge</button>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No active challenges at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Right: Sidebar -->
    <div class="sidebar">
      <div class="rank">Current Rank: <span>N/A</span></div>
      <div style="margin-bottom:25px;">
        <h4 style="margin-bottom:8px;">🏆 Leaderboard (Top 10)</h4>
        <ol style="padding-left:20px; color:#fff;">
          <?php foreach ($leaderboard as $team): ?>
            <li>
              <strong><?php echo htmlspecialchars($team['team_name']); ?></strong>
              - <?php echo (int)$team['total_points']; ?> pts
            </li>
          <?php endforeach; ?>
          <?php if (empty($leaderboard)): ?>
            <li>No teams yet.</li>
          <?php endif; ?>
        </ol>
      </div>
      <ul class="sidebar-links">
        <li><a href="#">🏆 Current Leaderboard</a></li>
        <li><a href="#">📊 Compare Progress</a></li>
        <li><a href="#">📝 Review Submissions</a></li>
      </ul>
    </div>
  </div>

<script src="script.js"></script>
</body>
</html>
