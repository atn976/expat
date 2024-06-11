<?php
include 'db.php';

$response = [];

if (isset($_POST['title']) && isset($_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = isset($_POST['category']) ? $_POST['category'] : null;

    $stmt = $conn->prepare("INSERT INTO article (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    if ($stmt->execute()) {
        $article_id = $stmt->insert_id;
        
        if ($category_id) {
            $stmt = $conn->prepare("INSERT INTO article_has_category (article_id, category_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $article_id, $category_id);
            $stmt->execute();
        }
        
        $response['success'] = true;
        $response['redirect'] = $category_id ? "list_articles.php?category_id=$category_id" : "list_articles.php";
    } else {
        $response['success'] = false;
        $response['message'] = "Error inserting article.";
    }

    $stmt->close();
} else {
    $response['success'] = false;
    $response['message'] = "All fields (title and content) are required.";
}

echo json_encode($response);
$conn->close();
