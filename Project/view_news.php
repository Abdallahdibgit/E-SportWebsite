<?php
include 'Connection.php';

try {
    $sql = $pdo->query("SELECT news_id, title, content, publish_date, image FROM news");
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching news: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News</title>
    <style>
        .news-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        .news-item img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Latest News</h1>
    <?php
    if (!empty($result)) {
        foreach ($result as $row) {
            echo "<div class='news-item'>";
            echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
            echo "<p>" . htmlspecialchars($row['content']) . "</p>";
            echo "<p><small>Published on: " . htmlspecialchars($row['publish_date']) . "</small></p>";
            if (!empty($row['image'])) {
                echo "<img src='" . htmlspecialchars($row['image']) . "' alt='News Image'>";
            }
            echo "</div>";
        }
    } else {
        echo "No news found.";
    }
    ?>
</body>
</html>
