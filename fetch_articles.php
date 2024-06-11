<?php
include 'db.php';

$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

if ($category_id !== '') {
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.content, c.name AS category
        FROM article a
        JOIN article_has_category ahc ON a.id = ahc.article_id
        JOIN category c ON c.id = ahc.category_id
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $category_id);
} else {
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.content, c.name AS category
        FROM article a
        LEFT JOIN article_has_category ahc ON a.id = ahc.article_id
        LEFT JOIN category c ON c.id = ahc.category_id
    ");
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='article'>";
        echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
        echo "<p>" . htmlspecialchars($row['content']) . "</p>";
        echo "<p><strong>Category:</strong> " . ($row['category'] ? htmlspecialchars($row['category']) : 'None') . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>No articles found.</p>";
}

$stmt->close();
$conn->close();
