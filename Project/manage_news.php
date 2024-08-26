<?php
session_start();
require_once 'Connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (!$pdo) {
    die("Could not connect to the database.");
}

function getAllNews($pdo) {
    $query = "SELECT * FROM news ORDER BY publish_date DESC";
    try {
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error retrieving news: " . $e->getMessage();
        return [];
    }
}

function createNews($pdo, $title, $content, $imagePath) {
    $query = "INSERT INTO news (title, content, publish_date, image) VALUES (?, ?, NOW(), ?)";
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$title, $content, $imagePath]);
    } catch (PDOException $e) {
        echo "Error creating news: " . $e->getMessage();
    }
}

function updateNews($pdo, $news_id, $title, $content, $imagePath = null) {
    try {
        if ($imagePath) {
            $query = "UPDATE news SET title = ?, content = ?, image = ? WHERE news_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$title, $content, $imagePath, $news_id]);
        } else {
            $query = "UPDATE news SET title = ?, content = ? WHERE news_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$title, $content, $news_id]);
        }
    } catch (PDOException $e) {
        echo "Error updating news: " . $e->getMessage();
    }
}

function deleteNews($pdo, $news_id) {
    try {
        $query = "DELETE FROM news WHERE news_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$news_id]);
    } catch (PDOException $e) {
        echo "Error deleting news: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $imagePath = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $imageName = basename($_FILES['image']['name']);
            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            // Check if the file is an image
            if (exif_imagetype($imageTmpPath)) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imagePath = $uploadDir . time() . '_' . $imageName;
                move_uploaded_file($imageTmpPath, $imagePath);
            } else {
                echo "Invalid file type. Please upload an image.";
            }
        }

        createNews($pdo, $title, $content, $imagePath);
    } elseif (isset($_POST['update'])) {
        $news_id = $_POST['news_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $imagePath = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES['image']['tmp_name'];
            $imageName = basename($_FILES['image']['name']);
            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageExtension, $allowedExtensions)) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imagePath = $uploadDir . time() . '_' . $imageName;
                move_uploaded_file($imageTmpPath, $imagePath);
            } else {
                echo "Invalid image file type. Allowed types: jpg, jpeg, png, gif.";
            }
        }

        updateNews($pdo, $news_id, $title, $content, $imagePath);
    } elseif (isset($_POST['delete'])) {
        deleteNews($pdo, $_POST['news_id']);
    }
}

$news = getAllNews($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage News</title>
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
        form {
            margin-bottom: 20px;
        }
        input, textarea, button {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: calc(100% - 22px);
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            width: auto;
            padding: 10px 20px;
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
        .image-preview {
            max-width: 100px;
            max-height: 100px;
        }
        .edit-form {
            display: none;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage News</h1>

        <form method="POST" enctype="multipart/form-data" id="news-form">
            <input type="hidden" name="news_id">
            <label>Title:</label>
            <input type="text" name="title" required><br>

            <label>Content:</label>
            <textarea name="content" rows="5" required></textarea><br>

            <label>Image:</label>
            <input type="file" name="image" accept="image/*"><br>

            <button type="submit" name="create" id="publish-btn">Publish News</button>
            <button type="submit" name="update" id="edit-btn" style="display: none;">Edit News</button>
        </form>

        <h2>Existing News Articles</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Publish Date</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($news as $article): ?>
                <tr data-id="<?php echo htmlspecialchars($article['news_id']); ?>">
                    <td><?php echo htmlspecialchars($article['news_id']); ?></td>
                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                    <td><?php echo htmlspecialchars($article['content']); ?></td>
                    <td><?php echo htmlspecialchars($article['publish_date']); ?></td>
                    <td>
                        <?php if (!empty($article['image']) && file_exists($article['image'])): ?>
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" class="image-preview" alt="News Image">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" onclick="editNews(<?php echo htmlspecialchars($article['news_id']); ?>)">Edit</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="news_id" value="<?php echo htmlspecialchars($article['news_id']); ?>">
                            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this news item?');">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function editNews(newsId) {
            const row = document.querySelector(`tr[data-id="${newsId}"]`);
            const title = row.querySelector('td:nth-child(2)').textContent;
            const content = row.querySelector('td:nth-child(3)').textContent;

            const form = document.getElementById('news-form');
            form.querySelector('input[name="news_id"]').value = newsId;
            form.querySelector('input[name="title"]').value = title;
            form.querySelector('textarea[name="content"]').value = content;

            document.getElementById('publish-btn').style.display = 'none';
            document.getElementById('edit-btn').style.display = 'inline';

            form.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
