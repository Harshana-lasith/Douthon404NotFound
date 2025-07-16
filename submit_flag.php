<?php
session_start();
include 'db_connection.php'; // Include your DB connection

$team_id = $_SESSION['team_id']; // Assuming team is logged in
$challenge_id = $_POST['challenge_id'];
$submitted_flag = trim($_POST['flag']);

// 1. Fetch correct flag from DB
$sql = "SELECT flag FROM challenges WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$stmt->bind_result($correct_flag);
$stmt->fetch();
$stmt->close();

if ($submitted_flag === $correct_flag) {
    // 2. Update team progress
    $update = "INSERT INTO solved_challenges (team_id, challenge_id) VALUES (?, ?)";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("ii", $team_id, $challenge_id);
    $stmt2->execute();
    $stmt2->close();

    // 3. Redirect to buildathon task
    header("Location: buildathon.php?challenge_id=" . $challenge_id);
    exit;
} else {
    echo "<script>alert('❌ Incorrect flag. Try again.'); window.history.back();</script>";
}
?>
