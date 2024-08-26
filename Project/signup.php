<?php
session_start();

try {
    $pdo = new PDO('mysql:host=localhost;dbname=project', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);

    if (!empty($email) && !empty($password) && !empty($name)) {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error_message = "Email already exists. Please log in.";
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (:email, :password, :name)");
                $stmt->execute(['email' => $email, 'password' => $hashed_password, 'name' => $name]);
                $success_message = "User sign-up successful! You can now log in.";
            } catch (PDOException $e) {
                $error_message = "Error during user sign-up: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up as User</title>
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
        .signup-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 350px;
        }
        .signup-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .signup-container input[type="email"],
        .signup-container input[type="password"],
        .signup-container input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .signup-container button {
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
        .signup-container button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }
        .success {
            color: green;
            margin-bottom: 10px;
            text-align: center;
        }
        .link {
            text-align: center;
            display: block;
            margin-top: 10px;
            color: #4CAF50;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Sign Up as User</h2>
    <?php if (!empty($error_message)): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <form method="POST" action="signup.php">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>
    <a href="login.php" class="link">Back to Login</a>
</div>

</body>
</html>
