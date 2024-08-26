<?php
session_start();
require_once 'Connection.php';

$error_message = '';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $feedback_id = $_POST['feedback_id'];
    $admin_reply = trim($_POST['admin_reply']);

    if (!empty($admin_reply)) {
        $stmt = $pdo->prepare("UPDATE feedback SET admin_reply = ? WHERE feedback_id = ?");
        $stmt->execute([$admin_reply, $feedback_id]);
        header("Location: list_feedback.php");
        exit();
    }
}

$stmt = $pdo->query("SELECT f.feedback_id, f.user_id, f.feedback_date, f.feedback_content, f.admin_reply, u.name AS user_name 
                     FROM feedback f
                     JOIN users u ON f.user_id = u.user_id
                     ORDER BY f.feedback_date DESC");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Feedback</title>
    <link rel="stylesheet" href="../style.css">
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
        }
        .reply-form {
            margin-top: 10px;
        }
        .reply-form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .reply-form button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .reply-form button:hover {
            background-color: #5a5a5f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Feedback List</h2>
        <table>
            <thead>
                <tr>
                    <th>Feedback ID</th>
                    <th>User Name</th>
                    <th>Date</th>
                    <th>Content</th>
                    <th>Admin Reply</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td><?php echo htmlspecialchars($feedback['feedback_id']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['feedback_date']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($feedback['feedback_content'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($feedback['admin_reply'])); ?></td>
                    <td>
                        <form method="POST" action="list_feedback.php" class="reply-form">
                            <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($feedback['feedback_id']); ?>">
                            <textarea name="admin_reply" rows="3" placeholder="Enter your reply here..."><?php echo htmlspecialchars($feedback['admin_reply']); ?></textarea>
                            <button type="submit" name="reply">Submit Reply</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
