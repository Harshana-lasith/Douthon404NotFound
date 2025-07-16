<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'team';

    // Check if username or email exists
    $stmt2 = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt2->bind_param("ss", $username, $email);
    $stmt2->execute();
    $stmt2->store_result();

    if ($stmt2->num_rows > 0) {
        $error = "Username or Email already taken!";
    } else {
        if ($_POST['team_option'] === 'create') {
            // Create new team
            $team_name = $_POST['team_name'];
            // Check if team name exists
            $stmt = $conn->prepare("SELECT id FROM teams WHERE team_code = ?");
            $stmt->bind_param("s", $team_name);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Team name already taken!";
            } else {
                // Insert user
                $user_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $user_stmt->bind_param("ssss", $username, $email, $password, $role);
                $user_stmt->execute();
                $user_id = $user_stmt->insert_id;

                // Generate unique team code
                do {
                    $team_code = substr(md5(uniqid(rand(), true)), 0, 8);
                    $check = $conn->prepare("SELECT id FROM teams WHERE team_code = ?");
                    $check->bind_param("s", $team_code);
                    $check->execute();
                    $check->store_result();
                } while ($check->num_rows > 0);

                // Insert team
                $team_stmt = $conn->prepare("INSERT INTO teams (team_name, team_code, user_id) VALUES (?, ?, ?)");
                $team_stmt->bind_param("ssi", $team_name, $team_code, $user_id);
                $team_stmt->execute();

                // Update user's team_id
                $team_id = $conn->insert_id;
                $update = $conn->prepare("UPDATE users SET team_id = ? WHERE id = ?");
                $update->bind_param("ii", $team_id, $user_id);
                $update->execute();

                header("Location: login.php");
                exit();
            }
        } elseif ($_POST['team_option'] === 'join') {
            // Join existing team
            $team_code = $_POST['team_code'];
            $stmt = $conn->prepare("SELECT id FROM teams WHERE team_code = ?");
            $stmt->bind_param("s", $team_code);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($team_id);
                $stmt->fetch();

                // Insert user
                $user_stmt = $conn->prepare("INSERT INTO users (username, email, password, role, team_id) VALUES (?, ?, ?, ?, ?)");
                $user_stmt->bind_param("ssssi", $username, $email, $password, $role, $team_id);
                $user_stmt->execute();

                header("Location: login.php");
                exit();
            } else {
                $error = "Invalid team code!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
    /* ...existing styles... */
    .team-option-group { margin-bottom: 18px; }
    .team-option-group label { margin-right: 18px; }
        body {
      background: #1b0f17;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: #ffffff;
    }

    .login-container {
      background: #2d1b2f;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(255, 77, 77, 0.3);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .title {
      font-size: 36px;
      font-weight: bold;
      color: #ff4d4d;
      margin-bottom: 5px;
    }
    .version {
      display: inline-block;
      background-color: #ff4d4d;
      color: #ffffff;
      padding: 2px 8px;
      border-radius: 5px;
      font-size: 18px;
      margin-left: 8px;
    }

    .tagline {
      color: #ffc0cb;
      font-size: 16px;
      margin-bottom: 30px;
    }

    input[type="text"],
    input[type="password"],
    input[type="email"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0 20px;
      border: none;
      border-radius: 5px;
      background: #3a2d3c;
      color: #ffffff;
      font-size: 16px;
    }

    input[type="submit"] {
      background: #ff4d4d;
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }

    button[type="submit"]:hover {
      background: #e03e3e;
    }

    .footer {
      margin-top: 20px;
      font-size: 14px;
      color: #888;
    }</style>
   
    <script>
    function toggleTeamFields() {
        var createFields = document.getElementById('create-team-fields');
        var joinFields = document.getElementById('join-team-fields');
        if (document.getElementById('create').checked) {
            createFields.style.display = 'block';
            joinFields.style.display = 'none';
        } else {
            createFields.style.display = 'none';
            joinFields.style.display = 'block';
        }
    }
    </script>
</head>
<body>
<form method="POST" action="signup.php" class="login-container">
    <div class="title">Duothan<span class="version">5.0</span></div>
    <div class="tagline">Crack the code, <strong>Create the Future</strong></div>
    <?php if (isset($error)) echo "<div style='color:#ff4d4d;margin-bottom:10px;'>$error</div>"; ?>
    <div class="team-option-group">
        <label><input type="radio" name="team_option" id="create" value="create" checked onclick="toggleTeamFields()"> Create Team</label>
        <label><input type="radio" name="team_option" id="join" value="join" onclick="toggleTeamFields()"> Join Team</label>
    </div>
    <div id="create-team-fields">
        <input type="text" name="team_name" placeholder="Team Name">
    </div>
    <div id="join-team-fields" style="display:none;">
        <input type="text" name="team_code" placeholder="Team Code">
    </div>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Sign Up</button>
    <a href="login.php">Login</a>
</form>
<script>toggleTeamFields();</script>
</body>
</html>
\