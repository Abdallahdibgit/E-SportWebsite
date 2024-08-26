<?php
require_once 'Connection.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $tournament_name = $_POST['tournament_name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Ensure start_date is today or in the future
        if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
            echo json_encode(['error' => 'Start date must be today or a future date']);
            exit();
        }

        $imageName = null;
        if (isset($_FILES['tournament_image']) && $_FILES['tournament_image']['error'] == UPLOAD_ERR_OK) {
            $imageName = time() . '_' . $_FILES['tournament_image']['name'];
            $target = 'uploads/' . $imageName;
            if (!move_uploaded_file($_FILES['tournament_image']['tmp_name'], $target)) {
                echo json_encode(['error' => 'Failed to upload image']);
                exit();
            }
        }

        $stmt = $pdo->prepare("INSERT INTO tournaments (tournament_name, description, start_date, end_date, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$tournament_name, $description, $start_date, $end_date, $imageName]);

        $id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($tournament);

    } elseif ($action == 'delete') {
        $id = $_POST['tournament_id'];

        // Ensure the winner is shown before deleting
        $stmt = $pdo->prepare("SELECT winner FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tournament['winner'] === null) {
            echo json_encode(['error' => 'Cannot delete the tournament before showing the winner']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT image FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tournament['image'] && file_exists('uploads/' . $tournament['image'])) {
            unlink('uploads/' . $tournament['image']);
        }

        $stmt = $pdo->prepare("DELETE FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['id' => $id]);

    } elseif ($action == 'edit') {
        $id = $_POST['tournament_id'];
        $tournament_name = $_POST['tournament_name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Ensure start_date is today or in the future
        if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
            echo json_encode(['error' => 'Start date must be today or a future date']);
            exit();
        }

        $imageName = $_POST['existing_image'];
        if (isset($_FILES['tournament_image']) && $_FILES['tournament_image']['error'] == UPLOAD_ERR_OK) {
            $imageName = time() . '_' . $_FILES['tournament_image']['name'];
            $target = 'uploads/' . $imageName;
            if (!move_uploaded_file($_FILES['tournament_image']['tmp_name'], $target)) {
                echo json_encode(['error' => 'Failed to upload image']);
                exit();
            }

            $stmt = $pdo->prepare("SELECT image FROM tournaments WHERE tournament_id = ?");
            $stmt->execute([$id]);
            $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tournament['image'] && file_exists('uploads/' . $tournament['image'])) {
                unlink('uploads/' . $tournament['image']);
            }
        }

        $stmt = $pdo->prepare("UPDATE tournaments SET tournament_name = ?, description = ?, start_date = ?, end_date = ?, image = ? WHERE tournament_id = ?");
        $stmt->execute([$tournament_name, $description, $start_date, $end_date, $imageName, $id]);

        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($tournament);

    } elseif ($action == 'get') {
        $id = $_POST['tournament_id'];
        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
        $stmt->execute([$id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($tournament);

    } elseif ($action == 'get_all') {
        $stmt = $pdo->query("SELECT * FROM tournaments");
        $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tournaments);
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tournaments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .mt {
            color: black;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-top: 20px;
        }
        #add-tournament-form,
        #edit-tournament-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #add-tournament-form input,
        #add-tournament-form button,
        #edit-tournament-container input,
        #edit-tournament-container button {
            width: calc(100% - 22px);
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        #add-tournament-form button,
        #edit-tournament-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        #add-tournament-form button:hover,
        #edit-tournament-container button:hover {
            background-color: #5a5a5f;
        }
        #tournaments-table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #tournaments-table th,
        #tournaments-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        #tournaments-table th {
            background-color: #f4f4f4;
            color: #333;
        }
        #tournaments-table tr:hover {
            background-color: #f4f4f4;
        }
        #tournaments-table button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        #tournaments-table button:hover {
            background-color: #5a5a5f;
        }
        #tournaments-table img {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>

<h2 class="mt">Manage Tournaments</h2>
<form id="add-tournament-form" enctype="multipart/form-data">
    <input type="text" name="tournament_name" placeholder="Tournament Name" required>
    <input type="text" name="description" placeholder="Description" required>
    <input type="date" name="start_date" required>
    <input type="date" name="end_date" required>
    <input type="file" name="tournament_image">
    <button type="button" onclick="addTournament()">Add Tournament</button>
</form>

<div id="edit-tournament-container" style="display: none;">
    <h3>Edit Tournament</h3>
    <form id="edit-tournament-form" enctype="multipart/form-data">
        <input type="hidden" id="edit-tournament-id" name="tournament_id">
        <input type="text" id="edit-tournament-name" name="tournament_name" placeholder="Tournament Name" required>
        <input type="text" id="edit-description" name="description" placeholder="Description" required>
        <input type="date" id="edit-start-date" name="start_date" required>
        <input type="date" id="edit-end-date" name="end_date" required>
        <input type="hidden" id="edit-existing-image" name="existing_image">
        <input type="file" id="edit-tournament-image" name="tournament_image">
        <button type="button" onclick="editTournament()">Save Changes</button>
    </form>
</div>

<table id="tournaments-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="tournaments-body">
    </tbody>
</table>

<script>
    function validateDates(start_date, end_date) {
        const currentDate = new Date();
        const startDate = new Date(start_date);
        const endDate = new Date(end_date);

        if (startDate < currentDate) {
            alert('The start date cannot be in the past.');
            return false;
        }

        if (endDate < startDate) {
            alert('The end date cannot be before the start date.');
            return false;
        }

        return true;
    }

    function fetchTournaments() {
        fetch('tournaments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'get_all' })
        })
        .then(response => response.json())
        .then(tournaments => {
            const tableBody = document.getElementById('tournaments-body');
            tableBody.innerHTML = '';
            tournaments.forEach(tournament => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${tournament.tournament_id}</td>
                    <td>${tournament.tournament_name}</td>
                    <td>${tournament.description}</td>
                    <td>${tournament.start_date}</td>
                    <td>${tournament.end_date}</td>
                    <td><img src="uploads/${tournament.image}" alt="${tournament.tournament_name}" width="100"></td>
                    <td>
                        <button onclick="editTournamentForm(${tournament.tournament_id})">Edit</button>
                        <button onclick="deleteTournament(${tournament.tournament_id})">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        });
    }

    function addTournament() {
        const start_date = document.querySelector('input[name="start_date"]').value;
        const end_date = document.querySelector('input[name="end_date"]').value;

        if (!validateDates(start_date, end_date)) {
            return;
        }

        const formData = new FormData(document.getElementById('add-tournament-form'));
        formData.append('action', 'add');
        fetch('tournaments.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(tournament => {
            if (tournament.error) {
                alert(tournament.error);
                return;
            }
            fetchTournaments();
        });
    }

    function deleteTournament(id) {
        if (confirm('Are you sure you want to delete this tournament?')) {
            fetch('tournaments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'delete', tournament_id: id })
            })
            .then(response => response.json())
            .then(result => {
                fetchTournaments();
            });
        }
    }

    function editTournamentForm(id) {
        fetch('tournaments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'get', tournament_id: id })
        })
        .then(response => response.json())
        .then(tournament => {
            document.getElementById('edit-tournament-id').value = tournament.tournament_id;
            document.getElementById('edit-tournament-name').value = tournament.tournament_name;
            document.getElementById('edit-description').value = tournament.description;
            document.getElementById('edit-start-date').value = tournament.start_date;
            document.getElementById('edit-end-date').value = tournament.end_date;
            document.getElementById('edit-existing-image').value = tournament.image;

            document.getElementById('edit-tournament-container').style.display = 'block';
        });
    }

    function editTournament() {
        const start_date = document.querySelector('#edit-start-date').value;
        const end_date = document.querySelector('#edit-end-date').value;

        if (!validateDates(start_date, end_date)) {
            return;
        }

        const formData = new FormData(document.getElementById('edit-tournament-form'));
        formData.append('action', 'edit');
        fetch('tournaments.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(tournament => {
            if (tournament.error) {
                alert(tournament.error);
                return;
            }
            document.getElementById('edit-tournament-container').style.display = 'none';
            fetchTournaments();
        });
    }

    fetchTournaments();
</script>
</body>
</html>
