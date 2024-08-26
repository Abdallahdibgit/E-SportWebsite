<?php
session_start();
require_once 'Connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_status = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT team_id, team_name FROM teams WHERE team_id NOT IN (SELECT team_id FROM team_members WHERE user_id = ?)");
$stmt->execute([$user_id]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT team_id, team_name FROM teams WHERE team_id IN (SELECT team_id FROM team_members WHERE user_id = ?)");
$stmt->execute([$user_id]);
$current_team = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['join_team'])) {
        $team_id = $_POST['team_id'];

        if ($current_team) {
            $_SESSION['error'] = "You must leave your current team before joining a new one.";
            header('Location: join_team.php');
            exit();
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) as player_count FROM team_members WHERE team_id = ?");
        $stmt->execute([$team_id]);
        $player_count = $stmt->fetchColumn();

        if ($player_count < 4) {
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
            $stmt->execute([$team_id, $user_id]);

            $_SESSION['success'] = "You have successfully joined the team!";
            header('Location: join_team.php');
            exit();
        } else {
            $_SESSION['error'] = "Sorry, this team is already full.";
        }
    } elseif (isset($_POST['leave_team'])) {
        $team_id = $_POST['team_id'];

        $stmt = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
        $stmt->execute([$team_id, $user_id]);

        $_SESSION['success'] = "You have successfully left the team!";
        header('Location: join_team.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join/Leave Team</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-bottom: 20px;
        }
        select, button {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a5a5f;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid transparent;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .disabled {
            background-color: #e0e0e0;
            color: #9e9e9e;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Join or Leave a Team</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if ($user_status === 'pending' || $user_status === 'rejected'): ?>
        <p>Your account is currently <?php echo htmlspecialchars($user_status); ?>. Please wait until an admin approves you to join a team.</p>
    <?php else: ?>
        <?php if ($current_team): ?>
            <h3>Your Current Team</h3>
            <p>You are currently a member of the team: <strong><?php echo htmlspecialchars($current_team['team_name']); ?></strong></p>
            <form action="join_team.php" method="post">
                <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($current_team['team_id']); ?>">
                <button type="submit" name="leave_team">Leave This Team</button>
            </form>
        <?php else: ?>
            <p>You haven't joined any team.</p>
        <?php endif; ?>

        <h3>Join a Team</h3>
        <form action="join_team.php" method="post" <?php if ($current_team): ?> class="disabled" <?php endif; ?>>
            <label for="team_id">Select a Team:</label>
            <select name="team_id" id="team_id" <?php if ($current_team): ?> disabled <?php endif; ?> required>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo htmlspecialchars($team['team_id']); ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit" name="join_team" <?php if ($current_team): ?> disabled <?php endif; ?>>Join Team</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
