<?php
declare(strict_types=1);
header('Content-Type: application/json');
include 'db.php';

$response = [];

if (isset($_GET['id'])) {
    $article_id = (int) $_GET['id'];

    $stmt = $conn->prepare("SELECT id, title, content, created_at, updated_at FROM article WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $response = $result->fetch_assoc();
        } else {
            $response['error'] = "Article not found.";
        }
    } else {
        $response['error'] = "Error fetching article.";
    }
    $stmt->close();
} else {
    $response['error'] = "Invalid request.";
}

echo json_encode($response);
$conn->close();
