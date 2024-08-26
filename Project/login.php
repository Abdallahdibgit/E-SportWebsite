<?php
session_start();
require_once 'Connection.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (!empty($email) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['role'] = 'user';
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                
                header("Location: player-index.php");
                exit();
            } else {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
                $stmt->execute(['email' => $email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['role'] = 'admin';
                    $_SESSION['admin_id'] = $admin['admin_id']; // Set admin_id here
                    $_SESSION['name'] = $admin['name'];
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Invalid email or password.";
                }
            }
        } else {
            $error_message = "Please enter both email and password.";
        }
    } else {
        $error_message = "Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #28282B;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #f4f4f9;
        }
        .login-container {
            background-color: #3b3b3d;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            width: 350px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #fff;
            font-size: 24px;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #555;
            border-radius: 5px;
            font-size: 14px;
            background-color: #1c1c1e;
            color: #fff;
            transition: border-color 0.3s ease;
        }
        .login-container input[type="email"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #4CAF50; /* Green color for input focus */
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .login-container button:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        .error {
            color: #f8d7da;
            background-color: #842029;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .link {
            display: block;
            margin-top: 10px;
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .link:hover {
            text-decoration: underline;
            color: #68d370;
        }
        .forgot-password {
            margin-top: 10px;
        }
        .forgot-password button {
            background: none;
            border: none;
            color: #4CAF50;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            transition: color 0.3s ease;
        }
        .forgot-password button:hover {
            text-decoration: none;
            color: white;
        }
        .back-homepage {
            margin-top: 10px;
        }
        .back-homepage button {
            background: none;
            border: none;
            color: #4CAF50;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            transition: color 0.3s ease;
        }
        .back-homepage button:hover {
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="signup.php" class="link">Sign Up as User</a>
        <div class="forgot-password">
            <button onclick="location.href='forgot_password.php'">Forgot Password?</button>
        </div>
        <div class="back-homepage">
            <button onclick="location.href='indexHome.php'">Back to Homepage</button>
        </div>
    </div>
</body>
</html>
