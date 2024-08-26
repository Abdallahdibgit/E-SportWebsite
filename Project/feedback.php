<?php
session_start();
require_once 'Connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $feedback = $_POST['feedback'];
    
    if (empty($feedback)) {
        $error = "Feedback cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, feedback_date, feedback_content) VALUES (:user_id, NOW(), :feedback)");
        $stmt->execute([
            'user_id' => $user_id,
            'feedback' => $feedback
        ]);
        
        $success = "Thank you for your feedback!";
    }
}

$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = :user_id ORDER BY feedback_date DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
            margin-top: 0;
        }
        .feedback-form {
            margin-bottom: 20px;
        }
        .feedback-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .feedback-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .feedback-form button:hover {
            background-color: #5a5a5f;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
        }
        .message.success {
            background-color: #28a745;
        }
        .message.error {
            background-color: #dc3545;
        }
        .feedback-list {
            margin-top: 20px;
        }
        .feedback-list .feedback-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .feedback-list .feedback-item .date {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Submit Feedback</h2>
    
    <?php if (isset($success)): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form class="feedback-form" method="post" action="">
        <textarea name="feedback" placeholder="Enter your feedback or report an issue here..."></textarea>
        <button type="submit">Submit Feedback</button>
    </form>
    
    <?php if (!empty($feedbacks)): ?>
        <div class="feedback-list">
            <h3>Your Previous Feedback</h3>
            <?php foreach ($feedbacks as $item): ?>
                <div class="feedback-item">
                    <p><?php echo nl2br(htmlspecialchars($item['feedback_content'])); ?></p>
                    <p class="date"><?php echo htmlspecialchars($item['feedback_date']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
