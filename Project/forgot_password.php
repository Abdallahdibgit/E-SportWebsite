<?php
session_start();
require_once 'Connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        // Check if the email exists in the users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(50));

            // Set an expiration date (e.g., 1 hour from now)
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Delete any existing tokens for this email
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
            $stmt->execute(['email' => $email]);

            // Save the new token to the password_resets table
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
            $stmt->execute([
                'email' => $email,
                'token' => $token,
                'expires_at' => $expires_at
            ]);

            // Send the reset link via email
            $reset_link = "http://yourwebsite.com/reset_password.php?token=$token";
            $subject = "Password Reset Request";
            $message = "You have requested to reset your password. Please click the following link to reset it: $reset_link. This link will expire in 1 hour.";
            $headers = "From: no-reply@yourwebsite.com\r\n";
            $headers .= "Content-type: text/html\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $success_message = "A reset link has been sent to your email address.";
            } else {
                $error_message = "Failed to send the reset link. Please try again later.";
            }
        } else {
            $error_message = "No account found with that email address.";
        }
    } else {
        $error_message = "Please enter your email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        .reset-password-container input[type="password"],
        .reset-password-container input[type="email"],
        .reset-password-container select {
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
        .reset-password-container p a {
            display: block;
            width: calc(100% - 24px);
            padding: 12px;
            margin: 10px auto 0;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }
        .reset-password-container button:hover {
            background-color: #45a049;
        }
        .reset-password-container p a:hover {
            background-color: #0056b3;
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
    </style>
</head>
<body>
    <div class="reset-password-container">
        <h2>Forgot Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <p><a href="login.php">Return Back</a></p>
    </div>
</body>
</html>
