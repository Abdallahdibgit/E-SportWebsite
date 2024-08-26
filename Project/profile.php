<?php
session_start();
include('Connection.php');

if (!isset($_SESSION['user_id'])) {
    die("No user is logged in.");
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found. Please check your session and database.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'update_info') {
            $name = htmlspecialchars($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

            if ($email === false) {
                $_SESSION['error'] = "Invalid email format.";
                header('Location: profile.php');
                exit();
            }

            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $user_id]);

            $_SESSION['success'] = "Profile updated successfully!";
            header('Location: profile.php');
            exit();
        } elseif ($action === 'upload_picture') {
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
                $fileName = $_FILES['profile_picture']['name'];
                $fileSize = $_FILES['profile_picture']['size'];
                $fileType = $_FILES['profile_picture']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $allowedfileExtensions = ['jpg', 'jpeg', 'png'];
                if (in_array($fileExtension, $allowedfileExtensions) && $fileSize < 5000000) {
                    $uploadFileDir = './uploads/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        if ($user['profile_picture']) {
                            @unlink($uploadFileDir . $user['profile_picture']);
                        }

                        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                        $stmt->execute([$newFileName, $user_id]);

                        $_SESSION['success'] = "Profile picture updated successfully!";
                    } else {
                        $_SESSION['error'] = "Error moving the uploaded file.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid file type or size. Please upload a JPG or PNG image under 5MB.";
                }
            } else {
                $_SESSION['error'] = "File upload error: " . $_FILES['profile_picture']['error'];
            }

            header('Location: profile.php');
            exit();
        } elseif ($action === 'delete_picture') {
            if ($user['profile_picture']) {
                $uploadFileDir = './uploads/';
                @unlink($uploadFileDir . $user['profile_picture']);

                $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL WHERE user_id = ?");
                $stmt->execute([$user_id]);

                $_SESSION['success'] = "Profile picture deleted successfully!";
            }

            header('Location: profile.php');
            exit();
        } elseif ($action === 'delete_account') {
            if ($user['profile_picture']) {
                $uploadFileDir = './uploads/';
                @unlink($uploadFileDir . $user['profile_picture']);
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);

            session_unset();
            session_destroy();
            header('Location: login.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile</title>
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
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="email"], input[type="file"], button {
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
        }
        button:hover {
            background-color: #5a5a5f;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid transparent;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .profile-picture-container {
            margin: 20px 0;
        }
        img {
            max-width: 150px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Profile</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="profile.php" method="post" enctype="multipart/form-data">
        <h3>Edit Information</h3>
        <input type="hidden" name="action" value="update_info">
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Name" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email" required>
        <button type="submit">Update Information</button>
    </form>

    <form action="profile.php" method="post" enctype="multipart/form-data">
        <h3>Profile Picture</h3>
        <?php if ($user['profile_picture']): ?>
            <div class="profile-picture-container">
                <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                <form action="profile.php" method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete_picture">
                    <button type="submit">Delete Profile Picture</button>
                </form>
                <form action="profile.php" method="post" enctype="multipart/form-data" style="display: inline;">
                    <input type="hidden" name="action" value="upload_picture">
                    <input type="file" name="profile_picture" accept="image/*">
                    <button type="submit">Upload New Picture</button>
                </form>
            </div>
        <?php else: ?>
            <p>No profile picture uploaded.</p>
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_picture">
                <input type="file" name="profile_picture" accept="image/*">
                <button type="submit">Upload New Picture</button>
            </form>
        <?php endif; ?>
    </form>

    <form action="profile.php" method="post" style="margin-top: 20px;">
        <input type="hidden" name="action" value="delete_account">
        <button type="submit" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete Account</button>
    </form>
</div>
</body>
</html>
