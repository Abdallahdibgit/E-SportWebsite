<?php
session_start();
include('Connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(["status" => "error", "message" => "The email address is already in use."]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $password]);
                $newUserId = $pdo->lastInsertId();
                echo json_encode([
                    "status" => "success",
                    "user" => [
                        "user_id" => $newUserId,
                        "name" => $name,
                        "email" => $email,
                        "status" => "pending"
                    ]
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => htmlspecialchars($e->getMessage())]);
        }
        exit();
    }

    if ($action === 'delete') {
        $id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success", "message" => "User deleted successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => htmlspecialchars($e->getMessage())]);
        }
        exit();
    }

    if ($action === 'edit') {
        $id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $id]);
            echo json_encode(["status" => "success", "message" => "User updated successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => htmlspecialchars($e->getMessage())]);
        }
        exit();
    }

    if ($action === 'approve') {
        $id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success", "message" => "User approved successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => htmlspecialchars($e->getMessage())]);
        }
        exit();
    }

    if ($action === 'reject') {
        $id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE user_id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success", "message" => "User rejected successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => htmlspecialchars($e->getMessage())]);
        }
        exit();
    }

    if ($action === 'kick') {
        $id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success", "message" => "User kicked out successfully."]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => htmlspecialchars($e->getMessage())]);
        }
        exit();
    }
}

$users = [];
try {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch data: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
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
        input, button {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a5a5f;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .message {
            margin-bottom: 20px;
        }
        #edit-user-form {
            display: none;
        }
        .return-button {
            display: block;
            margin: 20px 0;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .return-button:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

<h2>Users</h2>

<table id="user-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr id="user-<?php echo htmlspecialchars($user['user_id']); ?>">
                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['status']); ?></td>
                <td class="actions">
                    <?php if ($user['status'] === 'pending'): ?>
                        <button onclick="approveUser(<?php echo htmlspecialchars($user['user_id']); ?>)">Approve</button>
                        <button onclick="rejectUser(<?php echo htmlspecialchars($user['user_id']); ?>)">Reject</button>
                    <?php elseif ($user['status'] === 'approved'): ?>
                        <button onclick="kickUser(<?php echo htmlspecialchars($user['user_id']); ?>)">Kick</button>
                    <?php elseif ($user['status'] === 'rejected'): ?>
                        <button class="delete-button" onclick="deleteUser(<?php echo htmlspecialchars($user['user_id']); ?>)">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="index.php" class="return-button">Back to Index</a>

<script>
    function approveUser(userId) {
        const formData = new FormData();
        formData.append('action', 'approve');
        formData.append('user_id', userId);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.querySelector(`#user-${userId} td:nth-child(4)`).textContent = 'approved';
                document.querySelector(`#user-${userId} .actions`).innerHTML = '<button onclick="kickUser(' + userId + ')">Kick</button>';
            } else {
                alert(data.message);
            }
        });
    }

    function rejectUser(userId) {
        const formData = new FormData();
        formData.append('action', 'reject');
        formData.append('user_id', userId);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.querySelector(`#user-${userId} td:nth-child(4)`).textContent = 'rejected';
                document.querySelector(`#user-${userId} .actions`).innerHTML = '<button class="delete-button" onclick="deleteUser(' + userId + ')">Delete</button>';
            } else {
                alert(data.message);
            }
        });
    }

    function deleteUser(userId) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('user_id', userId);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.querySelector(`#user-${userId}`).remove();
            } else {
                alert(data.message);
            }
        });
    }

    function kickUser(userId) {
        const formData = new FormData();
        formData.append('action', 'kick');
        formData.append('user_id', userId);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.querySelector(`#user-${userId}`).remove();
            } else {
                alert(data.message);
            }
        });
    }
</script>

</body>
</html>
