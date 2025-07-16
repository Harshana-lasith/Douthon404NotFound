<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Check users table for username or email
    $stmt = $conn->prepare("SELECT id, username, role, password FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username, $role, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            if ($role === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user_id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['user_id'] = $user_id;
                header("Location: admin_dashboard.php");
                exit();
            } elseif ($role === 'team') {
                $_SESSION['user_id'] = $user_id; // Set user_id for team user
                // Get team info
                $team_stmt = $conn->prepare("SELECT id, team_name FROM teams WHERE user_id = ?");
                $team_stmt->bind_param("i", $user_id);
                $team_stmt->execute();
                $team_stmt->store_result();
                if ($team_stmt->num_rows === 1) {
                    $team_stmt->bind_result($team_id, $team_name);
                    $team_stmt->fetch();
                    $_SESSION['team_id'] = $team_id;
                    $_SESSION['team_name'] = $team_name;
                    header("Location: home.php");
                    exit();
                } else {
                    $error = "Team not found!";
                }
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Account not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Duothan 5.0 Login</title>
  <style>
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
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0 20px;
      border: none;
      border-radius: 5px;
      background: #3a2d3c;
      color: #ffffff;
      font-size: 16px;
    }

    button[type="submit"] {
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
    }
    .error-message {
      color: #ff4d4d;
      margin-bottom: 15px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="title">Duothan<span class="version">5.0</span></div>
    <div class="tagline">Crack the code, <strong>Create the Future</strong></div>
    <?php if (isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <input type="text" name="login" placeholder="Username / Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <div class="footer">
      © 2025 IEEE NSBM
    </div>
  </div>