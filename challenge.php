<?php
session_start();
include 'db.php';

if (!isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

$team_id = $_SESSION['team_id'];

if (!isset($_GET['id'])) {
    die("Challenge not specified.");
}

$challenge_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT title, description, flag, points FROM challenges WHERE id = ?");
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    die("Challenge not found.");
}

$stmt->bind_result($title, $description, $flag, $points);
$stmt->fetch();

// Check if already solved
$solved = false;
$check = $conn->prepare("SELECT id FROM submissions WHERE team_id = ? AND challenge_id = ?");
$check->bind_param("ii", $team_id, $challenge_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $solved = true;
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$solved) {
    $answer = trim($_POST['answer']);
    if ($answer === $flag) {
        // Store as solved with points
        $insert = $conn->prepare("INSERT INTO submissions (team_id, challenge_id, points) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $team_id, $challenge_id, $points);
        $insert->execute();
        $success = true;
        $solved = true;
    } else {
        $error = "Incorrect answer. Try again!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Challenge</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
        }

        p {
            line-height: 1.6;
            margin: 10px 0;
        }

        .points {
            font-size: 1.2em;
            font-weight: bold;
            color: #e67e22;
        }

        form {
            margin-top: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            margin-bottom: 10px;
        }

        button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2ecc71;
        }

        .success, .error {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 1.1em;
            text-align: center;
        }

        .success {
            background-color: #2ecc71;
            color: white;
        }

        .error {
            background-color: #e74c3c;
            color: white;
        }

        .solved {
            background-color: #ecf0f1;
            padding: 20px;
            border-left: 5px solid #2ecc71;
            margin-top: 20px;
        }

        .solved p {
            font-weight: bold;
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($title); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($description)); ?></p>
        <p class="points"><strong>Points:</strong> <?php echo (int)$points; ?></p>

        <?php if ($solved): ?>
            <div class="solved">
                <p>Congratulations! Challenge Solved! You earned <?php echo (int)$points; ?> points.</p>
                <p><strong>The flag is:</strong> <?php echo htmlspecialchars($flag); ?></p>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="answer" placeholder="Enter your answer" required>
                <button type="submit">Submit</button>
            </form>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
