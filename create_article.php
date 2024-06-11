<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Article</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="create_article.php">Create Article</a></li>
            <li><a href="list_articles.php">List Articles</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Create Article</h1>
        <form action="create_article.php" method="post">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            
            <label for="content">Content:</label>
            <textarea id="content" name="content" required></textarea>
            
            <label for="category">Category:</label>
            <select id="category" name="category">
                <option value="">Select Category</option>
                <?php
                include 'db.php';
                $sql = "SELECT * FROM category";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                }
                ?>
            </select>
            
            <button type="submit">Save</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $category_id = $_POST['category'];

            $stmt = $conn->prepare("INSERT INTO article (title, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $content);
            if ($stmt->execute()) {
                $article_id = $stmt->insert_id;
                if (!empty($category_id)) {
                    $stmt = $conn->prepare("INSERT INTO article_has_category (article_id, category_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $article_id, $category_id);
                    $stmt->execute();
                }
                echo "<p>Article created successfully!</p>";
            } else {
                echo "<p>Error creating article.</p>";
            }
            $stmt->close();
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
