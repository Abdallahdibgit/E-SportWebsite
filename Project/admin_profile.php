<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

include('Connection.php');

$admin_id = $_SESSION['admin_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        die('Admin not found');
    }
} catch (PDOException $e) {
    die("Error fetching admin details: " . htmlspecialchars($e->getMessage()));
}

$update_success = false;
$error_message = '';

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile information
    if (isset($_POST['update_profile'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];

        try {
            $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE admin_id = ?");
            $stmt->execute([$full_name, $email, $admin_id]);
            $update_success = true;
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . htmlspecialchars($e->getMessage());
        }
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } elseif (password_verify($old_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
                $stmt->execute([$hashed_password, $admin_id]);
                $update_success = true;
            } catch (PDOException $e) {
                $error_message = "Error updating password: " . htmlspecialchars($e->getMessage());
            }
        } else {
            $error_message = "Old password is incorrect.";
        }
    }

    // Handle profile picture upload
    if (isset($_POST['upload_picture'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $image_name = $_FILES['profile_picture']['name'];
            $image_tmp = $_FILES['profile_picture']['tmp_name'];
            $image_path = 'uploads/' . $image_name;

            if (move_uploaded_file($image_tmp, $image_path)) {
                try {
                    $stmt = $pdo->prepare("UPDATE admins SET profile_picture = ? WHERE admin_id = ?");
                    $stmt->execute([$image_path, $admin_id]);
                    $update_success = true;
                } catch (PDOException $e) {
                    $error_message = "Error uploading profile picture: " . htmlspecialchars($e->getMessage());
                }
            } else {
                $error_message = "Failed to upload the image.";
            }
        } else {
            $error_message = "No file was uploaded.";
        }
    }

    // Handle profile picture deletion
    if (isset($_POST['delete_picture'])) {
        try {
            $stmt = $pdo->prepare("UPDATE admins SET profile_picture = NULL WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            $update_success = true;
        } catch (PDOException $e) {
            $error_message = "Error deleting profile picture: " . htmlspecialchars($e->getMessage());
        }
    }

    // Refresh the admin data after update
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .profile-picture {
            text-align: center;
            margin-bottom: 15px;
        }

        .profile-picture img {
            max-width: 150px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .form-actions {
            text-align: center;
        }

        .form-actions input[type="submit"],
        .form-actions button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 5px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .form-actions input[type="submit"]:hover,
        .form-actions button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            text-align: center;
        }

        .success-message {
            color: green;
            text-align: center;
        }

        /* Style for file input and buttons */
        #profile_picture {
            padding: 10px;
            border: 1px solid #007bff;
            border-radius: 5px;
            display: block;
            margin: 0 auto 10px auto;
            background-color: #f9f9f9;
            color: #007bff;
        }

        #upload_btn,
        #delete_btn,
        #return_btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 5px;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: block;
            margin: 0 auto;
        }

        #upload_btn:hover,
        #delete_btn:hover,
        #return_btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function validatePasswordChange() {
            var newPassword = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            if (newPassword !== confirmPassword) {
                alert("New passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</head>

<body>
    <div class="container">
        <h2>Admin Profile</h2>
        <?php if (isset($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>
        <?php if ($update_success) { ?>
            <div class="success-message">Profile updated successfully!</div>
        <?php } ?>

        <!-- Profile Update Form -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                    value="<?php echo htmlspecialchars($admin['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>"
                    required>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <div class="profile-picture">
                    <?php if ($admin['profile_picture']) { ?>
                    <img src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture">
                    <button type="submit" name="delete_picture" id="delete_btn">Delete Picture</button>
                    <?php } else { ?>
                    <img src="default_profile.png" alt="Default Profile Picture">
                    <?php } ?>
                </div>
                <input type="file" id="profile_picture" name="profile_picture">
                <button type="submit" name="upload_picture" id="upload_btn">Upload Picture</button>
            </div>
            <div class="form-actions">
                <input type="submit" name="update_profile" value="Update Profile">
            </div>
        </form>

        <!-- Change Password Form -->
        <form method="POST" onsubmit="return validatePasswordChange();">
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <input type="submit" name="change_password" value="Change Password">
            </div>
        </form>

        <div class="form-actions">
            <a href="index.php"><button type="button" id="return_btn">Return to Home</button></a>
        </div>
    </div>
</body>

</html>
