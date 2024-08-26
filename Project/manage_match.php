<?php
require_once 'Connection.php';

function getAllMatches($pdo) {
    $query = "SELECT m.match_id, t.tournament_name, t1.team_name AS team1, t2.team_name AS team2, m.match_date, m.team1_score, m.team2_score, m.winner_team_id
              FROM matches m
              JOIN tournaments t ON m.tournament_id = t.tournament_id
              JOIN teams t1 ON m.team1_id = t1.team_id
              JOIN teams t2 ON m.team2_id = t2.team_id";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createMatch($pdo, $tournament_id, $team1_id, $team2_id, $match_date) {
    // Ensure the match date is not in the past
    if (strtotime($match_date) < time()) {
        throw new Exception("The match date must be today or in the future.");
    }
    
    // Ensure team1 and team2 are not the same
    if ($team1_id == $team2_id) {
        throw new Exception("A team cannot play against itself.");
    }

    $query = "INSERT INTO matches (tournament_id, team1_id, team2_id, match_date) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$tournament_id, $team1_id, $team2_id, $match_date]);
}

function updateMatch($pdo, $match_id, $team1_score, $team2_score) {
    $winner_team_id = null;
    if ($team1_score > $team2_score) {
        $winner_team_id = getTeamIdFromMatch($pdo, $match_id, 'team1_id');
    } elseif ($team2_score > $team1_score) {
        $winner_team_id = getTeamIdFromMatch($pdo, $match_id, 'team2_id');
    }

    $query = "UPDATE matches SET team1_score = ?, team2_score = ?, winner_team_id = ? WHERE match_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$team1_score, $team2_score, $winner_team_id, $match_id]);
}

function deleteMatch($pdo, $match_id) {
    $query = "DELETE FROM matches WHERE match_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$match_id]);
}

function getAllTournaments($pdo) {
    $query = "SELECT tournament_id, tournament_name FROM tournaments";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllTeams($pdo) {
    $query = "SELECT team_id, team_name FROM teams";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeamIdFromMatch($pdo, $match_id, $team_column) {
    $query = "SELECT $team_column FROM matches WHERE match_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$match_id]);
    return $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create'])) {
            createMatch($pdo, $_POST['tournament_id'], $_POST['team1_id'], $_POST['team2_id'], $_POST['match_date']);
        } elseif (isset($_POST['update'])) {
            updateMatch($pdo, $_POST['match_id'], $_POST['team1_score'], $_POST['team2_score']);
        } elseif (isset($_POST['delete'])) {
            deleteMatch($pdo, $_POST['match_id']);
        }
    } catch (Exception $e) {
        $error_message = htmlspecialchars($e->getMessage());
    }
}

$matches = getAllMatches($pdo);
$tournaments = getAllTournaments($pdo);
$teams = getAllTeams($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Matches</title>
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
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select, button {
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
            width: auto;
        }
        button:hover {
            background-color: #5a5a5f;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .actions form {
            display: inline;
        }
        .actions button {
            background-color: #f44336;
            border: none;
            color: white;
            cursor: pointer;
        }
        .actions button:hover {
            background-color: #c62828;
        }
        .error {
            color: #f44336;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Matches</h1>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="tournament_id">Tournament:</label>
            <select name="tournament_id" id="tournament_id" required>
                <option value="">Select Tournament</option>
                <?php foreach ($tournaments as $tournament): ?>
                    <option value="<?php echo htmlspecialchars($tournament['tournament_id']); ?>">
                        <?php echo htmlspecialchars($tournament['tournament_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="team1_id">Team 1:</label>
            <select name="team1_id" id="team1_id" required>
                <option value="">Select Team 1</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo htmlspecialchars($team['team_id']); ?>">
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="team2_id">Team 2:</label>
            <select name="team2_id" id="team2_id" required>
                <option value="">Select Team 2</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo htmlspecialchars($team['team_id']); ?>">
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="match_date">Match Date:</label>
            <input type="datetime-local" name="match_date" id="match_date" min="<?php echo date('Y-m-d\TH:i'); ?>" required><br>

            <button type="submit" name="create">Create Match</button>
        </form>

        <h2>Existing Matches</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tournament</th>
                    <th>Team 1</th>
                    <th>Team 2</th>
                    <th>Date</th>
                    <th>Team 1 Score</th>
                    <th>Team 2 Score</th>
                    <th>Winner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?php echo htmlspecialchars($match['match_id']); ?></td>
                    <td><?php echo htmlspecialchars($match['tournament_name']); ?></td>
                    <td><?php echo htmlspecialchars($match['team1']); ?></td>
                    <td><?php echo htmlspecialchars($match['team2']); ?></td>
                    <td><?php echo htmlspecialchars($match['match_date']); ?></td>
                    <td><?php echo htmlspecialchars($match['team1_score']); ?></td>
                    <td><?php echo htmlspecialchars($match['team2_score']); ?></td>
                    <td><?php echo htmlspecialchars($match['winner_team_id']); ?></td>
                    <td class="actions">
                    <form method="POST">
                        <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['match_id']); ?>">
                        <input type="number" name="team1_score" placeholder="Team 1 Score" min="0" required>
                        <input type="number" name="team2_score" placeholder="Team 2 Score" min="0" required>
                        <button type="submit" name="update">Update</button>
                        <button type="submit" name="delete">Delete</button>
                    </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
