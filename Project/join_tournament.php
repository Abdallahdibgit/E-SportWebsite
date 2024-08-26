<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'Connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT team_id FROM team_members WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_team = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_team) {
    $_SESSION['error'] = "You need to join a team before you can join a tournament.";
    header('Location: join_team.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT tournament_id, tournament_name, start_date, image FROM tournaments 
    WHERE tournament_id NOT IN (
        SELECT tournament_id FROM tournament_teams WHERE team_id = ?
    ) AND start_date > NOW()
");
$stmt->execute([$user_team['team_id']]);
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['join_tournament'])) {
        $tournament_id = $_POST['tournament_id'];

        $stmt = $pdo->prepare("INSERT INTO tournament_teams (tournament_id, team_id) VALUES (?, ?)");
        $stmt->execute([$tournament_id, $user_team['team_id']]);

        $_SESSION['success'] = "Your team has successfully joined the tournament!";
        header('Location: join_tournament.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Tournament</title>
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
        h2 {
            color: #333;
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
        .tournament-details {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .tournament-details img {
            max-width: 100px;
            height: auto;
            margin-right: 15px;
            border-radius: 5px;
        }
        .tournament-details div {
            flex: 1;
        }
        button {
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #5a5a5f;
        }
        .tournament-image {
            max-width: 100px;
            height: auto;
        }
        .back-button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Join a Tournament</h2>

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

    <?php if ($user_team): ?>
        <p>You are currently a member of the team with ID: <strong><?php echo htmlspecialchars($user_team['team_id']); ?></strong></p>
    <?php else: ?>
        <p>You need to join a team before joining a tournament.</p>
    <?php endif; ?>

    <h3>Available Tournaments</h3>
    <?php if (!empty($tournaments)): ?>
        <form action="join_tournament.php" method="post">
            <?php foreach ($tournaments as $tournament): ?>
                <div class="tournament-details">
                    <?php if (!empty($tournament['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($tournament['image']); ?>" alt="Tournament Image" class="tournament-image">
                    <?php endif; ?>
                    <div>
                        <p>
                            <strong><?php echo htmlspecialchars($tournament['tournament_name']); ?></strong><br>
                            Starts on: <?php echo htmlspecialchars($tournament['start_date']); ?>
                        </p>
                        <input type="radio" id="tournament_<?php echo htmlspecialchars($tournament['tournament_id']); ?>" name="tournament_id" value="<?php echo htmlspecialchars($tournament['tournament_id']); ?>" required>
                        <label for="tournament_<?php echo htmlspecialchars($tournament['tournament_id']); ?>">Select this tournament</label>
                    </div>
                </div>
            <?php endforeach; ?><br><br>

            <button type="submit" name="join_tournament">Join Tournament</button>
        </form>
    <?php else: ?>
        <p>There are no tournaments available for registration at the moment.</p>
    <?php endif; ?>

    <a href="player-index.php" class="back-button">Back to Player Index</a>
</div>

</body>
</html>
