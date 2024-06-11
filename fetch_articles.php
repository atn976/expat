    <?php
    include 'db.php';

    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    $sql = "SELECT * FROM articles";
    if ($category_id > 0) {
        $sql .= " WHERE category_id = " . $category_id;
    }

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='article' data-id='" . $row['id'] . "'>";
            echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
            echo "<p>" . htmlspecialchars($row['content']) . "</p>";
            echo "<button class='delete-article' data-id='" . $row['id'] . "'>Delete</button>";
            echo "</div>";
        }
    } else {
        echo "<p>No articles found.</p>";
    }

    $conn->close();
    ?>
