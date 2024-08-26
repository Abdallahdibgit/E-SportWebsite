<?php
session_start();
require_once 'Connection.php';

$error_message = '';
$success_message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid and not expired
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
    $stmt->execute(['token' => $token]);
    $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset_request) {
        $error_message = "Invalid or expired token.";
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            if (!empty($new_password) && !empty($confirm_password)) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                    // Update the user's password
                    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
                    $stmt->execute(['password' => $hashed_password, 'email' => $reset_request['email']]);

                    // Delete the token after successful reset
                    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
                    $stmt->execute(['email' => $reset_request['email']]);

                    $success_message = "Your password has been reset successfully. You can now <a href='login.php'>log in</a>.";
                } else {
                    $error_message = "Passwords do not match.";
                }
            } else {
                $error_message = "Please enter and confirm your new password.";
            }
        }
    }
} else {
    $error_message = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-password-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 350px;
        }
        .reset-password-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .reset-password-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .reset-password-container button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .reset-password-container button:hover {
            background-color: #45a049;
        }
        .error, .success {
            margin-bottom: 10px;
            text-align: center;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .reset-password-container p a {
            display: inline-block;
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }
        .reset-password-container p a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (empty($success_message)): ?>
            <form method="POST" action="">
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
