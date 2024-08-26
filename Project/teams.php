<?php
include('Connection.php');

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $team_name = $_POST['team_name'];
        $description = $_POST['description'];
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;

        // Handle image upload
        if ($image && $image['error'] == 0) {
            $imagePath = 'uploads/' . basename($image['name']);
            move_uploaded_file($image['tmp_name'], $imagePath);
        } else {
            $imagePath = null;
        }

        $stmt = $pdo->prepare("INSERT INTO teams (team_name, description, image) VALUES (?, ?, ?)");
        $stmt->execute([$team_name, $description, $imagePath]);
        
        $id = $pdo->lastInsertId();
        $team = $pdo->query("SELECT * FROM teams WHERE team_id = $id")->fetch(PDO::FETCH_ASSOC);
        echo json_encode($team);
    } elseif ($action == 'edit') {
        $id = $_POST['team_id'];
        $team_name = $_POST['team_name'];
        $description = $_POST['description'];
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;

        // Handle image upload
        if ($image && $image['error'] == 0) {
            $imagePath = 'uploads/' . basename($image['name']);
            move_uploaded_file($image['tmp_name'], $imagePath);
        } else {
            $stmt = $pdo->prepare("SELECT image FROM teams WHERE team_id = ?");
            $stmt->execute([$id]);
            $existingImage = $stmt->fetchColumn();
            $imagePath = $existingImage;
        }

        $stmt = $pdo->prepare("UPDATE teams SET team_name = ?, description = ?, image = ? WHERE team_id = ?");
        $stmt->execute([$team_name, $description, $imagePath, $id]);

        $team = $pdo->query("SELECT * FROM teams WHERE team_id = $id")->fetch(PDO::FETCH_ASSOC);
        echo json_encode($team);
    } elseif ($action === 'update_image') {
        $team_id = $_POST['team_id'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Process file upload
            $file = $_FILES['image'];
            $upload_dir = 'uploads/';
            $file_path = $upload_dir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Update database record with new image path
                $stmt = $pdo->prepare("UPDATE teams SET image = ? WHERE team_id = ?");
                if ($stmt->execute([$file_path, $team_id])) {
                    echo json_encode(['success' => true, 'image_path' => $file_path]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false, 'message' => 'Image upload failed']);
    } elseif ($action === 'delete') {
        $team_id = $_POST['team_id'];
        // Delete team from database
        $stmt = $pdo->prepare("DELETE FROM teams WHERE team_id = ?");
        if ($stmt->execute([$team_id])) {
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Deletion failed']);
    } elseif ($action == 'add_player') {
        $team_id = $_POST['team_id'];
        $user_id = $_POST['user_id'];
        
        // Check if the user is already in a team
        $stmt = $pdo->prepare("SELECT team_id FROM team_players WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing_team_id = $stmt->fetchColumn();

        if ($existing_team_id) {
            echo json_encode(['success' => false, 'message' => 'Player is already in another team.']);
            exit();
        }

        // Add player to the team
        $stmt = $pdo->prepare("INSERT INTO team_players (team_id, user_id) VALUES (?, ?)");
        if ($stmt->execute([$team_id, $user_id])) {
            echo json_encode(['success' => true]);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Failed to add player.']);
    } elseif ($action === 'remove_player') {
        $team_id = $_POST['team_id'];
        $user_id = $_POST['user_id'];
    
        // Remove player from the team
        $stmt = $pdo->prepare("DELETE FROM team_players WHERE team_id = ? AND user_id = ?");
        if ($stmt->execute([$team_id, $user_id])) {
            echo json_encode(['success' => true]);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Failed to remove player.']);
    }
}

// Fetch teams and users
$teams = $pdo->query("SELECT * FROM teams")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * FROM users WHERE status = 'approved'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Manage Teams</title>
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
        }
        form {
            margin-bottom: 20px;
        }
        input, button, select {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid black;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a5a5c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #ddd;
            color: black;
        }
        td {
            color: black;
        }
        .message {
            margin-bottom: 20px;
        }
        #edit-user-form {
            display: none;
        }
        h2 {
            color: black;
        }
        img.team-image {
            max-width: 100px;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>

<h2>Manage Teams</h2>
<div class="container">
<form id="add-team-form" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add" />
    <input type="text" name="team_name" placeholder="Team Name" required />
    <textarea name="description" placeholder="Description" required></textarea>
    <input type="file" name="image" accept="image/*" />
    <button type="button" onclick="addTeam()">Add Team</button>
</form>

<table id="teams-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Team Name</th>
            <th>Description</th>
            <th>Image</th>
            <th>Players</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($teams as $team): ?>
        <tr id="team-<?php echo htmlspecialchars($team['team_id']); ?>">
            <td><?php echo htmlspecialchars($team['team_id']); ?></td>
            <td><?php echo htmlspecialchars($team['team_name']); ?></td>
            <td><?php echo htmlspecialchars($team['description']); ?></td>
            <td>
                <?php if ($team['image']): ?>
                    <img src="<?php echo htmlspecialchars($team['image']); ?>" class="team-image" />
                <?php endif; ?>
                <input type="file" id="edit-image-<?php echo htmlspecialchars($team['team_id']); ?>" accept="image/*" />
                <button onclick="updateImage(<?php echo htmlspecialchars($team['team_id']); ?>)">Change Image</button>
            </td>
            <td>
                <ul id="players-<?php echo htmlspecialchars($team['team_id']); ?>">
                    <?php
                    $players = $pdo->prepare("SELECT u.user_id, u.name FROM team_players tp JOIN users u ON tp.user_id = u.user_id WHERE tp.team_id = ?");
                    $players->execute([$team['team_id']]);
                    foreach ($players->fetchAll(PDO::FETCH_ASSOC) as $player) {
                        echo "<li>" . htmlspecialchars($player['name']) . " <button onclick=\"removePlayer(" . htmlspecialchars($team['team_id']) . ", " . htmlspecialchars($player['user_id']) . ")\">Remove</button></li>";
                    }
                    ?>
                </ul>
                <select id="add-player-<?php echo htmlspecialchars($team['team_id']); ?>">
                    <option value="">Select Player</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['user_id']); ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="addPlayer(<?php echo htmlspecialchars($team['team_id']); ?>)">Add Player</button>
            </td>
            <td>
                <button onclick="editTeam(<?php echo htmlspecialchars($team['team_id']); ?>)">Edit</button>
                <button onclick="deleteTeam(<?php echo htmlspecialchars($team['team_id']); ?>)">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="edit-user-form">
    <h3>Edit User</h3>
    <form id="edit-form">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="team_id" id="edit-team-id" />
        <input type="text" name="team_name" id="edit-team-name" required />
        <textarea name="description" id="edit-description" required></textarea>
        <input type="file" name="image" id="edit-image" accept="image/*" />
        <button type="button" onclick="updateTeam()">Save Changes</button>
    </form>
</div>

<script>
function sendRequest(formData, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'teams.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            callback(response);
        } else {
            alert('An error occurred while processing the request.');
        }
    };
    xhr.send(formData);
}

function addTeam() {
    var form = document.getElementById('add-team-form');
    var formData = new FormData(form);

    sendRequest(formData, function(response) {
        console.log('Response:', response); // Debugging line

        if (response) {
            var row = `<tr id="team-${response.team_id}">
                        <td>${response.team_id}</td>
                        <td>${response.team_name}</td>
                        <td>${response.description}</td>
                        <td>${response.image ? `<img src="${response.image}" class="team-image" />` : ''}</td>
                        <td>
                            <ul id="players-${response.team_id}"></ul>
                            <select id="add-player-${response.team_id}">
                                <option value="">Select Player</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user['user_id']); ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="addPlayer(${response.team_id})">Add Player</button>
                        </td>
                        <td>
                            <button onclick="editTeam(${response.team_id})">Edit</button>
                            <button onclick="deleteTeam(${response.team_id})">Delete</button>
                        </td>
                    </tr>`;
            document.querySelector('#teams-table tbody').insertAdjacentHTML('beforeend', row);
        } else {
            alert('Failed to add team.');
        }
    });
}


function updateTeam() {
    var form = document.getElementById('edit-form');
    var formData = new FormData(form);
    sendRequest(formData, function(response) {
        if (response) {
            var row = document.getElementById('team-' + response.team_id);
            row.children[1].textContent = response.team_name;
            row.children[2].textContent = response.description;
            var imageCell = row.children[3];
            if (response.image_path) {
                imageCell.innerHTML = `<img src="${response.image_path}" class="team-image" />`;
            }
        } else {
            alert('Failed to update team.');
        }
    });
}

function editTeam(team_id) {
    var row = document.getElementById('team-' + team_id);
    document.getElementById('edit-team-id').value = team_id;
    document.getElementById('edit-team-name').value = row.children[1].textContent;
    document.getElementById('edit-description').value = row.children[2].textContent;
    document.getElementById('edit-user-form').style.display = 'block';
}

function deleteTeam(team_id) {
    if (confirm('Are you sure you want to delete this team?')) {
        var formData = new FormData();
        formData.append('action', 'delete');
        formData.append('team_id', team_id);
        sendRequest(formData, function(response) {
            if (response.success) {
                var row = document.getElementById('team-' + team_id);
                row.parentNode.removeChild(row);
            } else {
                alert('Failed to delete team.');
            }
        });
    }
}

function updateImage(team_id) {
    var fileInput = document.getElementById('edit-image-' + team_id);
    if (fileInput.files.length > 0) {
        var formData = new FormData();
        formData.append('action', 'update_image');
        formData.append('team_id', team_id);
        formData.append('image', fileInput.files[0]);

        sendRequest(formData, function(response) {
            if (response.success) {
                var imageCell = document.querySelector('#team-' + team_id + ' td:nth-child(4)');
                imageCell.innerHTML = `<img src="${response.image_path}" class="team-image" />`;
            } else {
                alert(response.message);
            }
        });
    }
}

function addPlayer(team_id) {
    var playerSelect = document.getElementById('add-player-' + team_id);
    var user_id = playerSelect.value;

    if (user_id) {
        var formData = new FormData();
        formData.append('action', 'add_player');
        formData.append('team_id', team_id);
        formData.append('user_id', user_id);

        sendRequest(formData, function(response) {
            if (response.success) {
                var playerList = document.getElementById('players-' + team_id);
                var listItem = document.createElement('li');
                listItem.innerHTML = `${playerSelect.options[playerSelect.selectedIndex].text} <button onclick="removePlayer(${team_id}, ${user_id})">Remove</button>`;
                playerList.appendChild(listItem);
            } else {
                alert(response.message);
            }
        });
    }
}

function removePlayer(team_id, user_id) {
    if (confirm('Are you sure you want to remove this player from the team?')) {
        var formData = new FormData();
        formData.append('action', 'remove_player');
        formData.append('team_id', team_id);
        formData.append('user_id', user_id);

        sendRequest(formData, function(response) {
            if (response.success) {
                // Remove player from the UI
                var playerList = document.getElementById('players-' + team_id);
                var items = playerList.getElementsByTagName('li');
                for (var i = 0; i < items.length; i++) {
                    if (items[i].innerHTML.includes(user_id)) {
                        playerList.removeChild(items[i]);
                        break;
                    }
                }
            } else {
                alert(response.message);
            }
        });
    }
}
</script>
</body>
</html>
