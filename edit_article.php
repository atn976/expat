<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
include 'db.php';

$response = [];

// Fonction pour sécuriser les entrées utilisateur
function sanitize_input(string $data): string {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || !isset($_POST['title']) || !isset($_POST['content'])) {
        $response['success'] = false;
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    $article_id = (int) $_POST['id'];
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);

    if (empty($title) || empty($content)) {
        $response['success'] = false;
        $response['message'] = "Title and content cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE article SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $article_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = "Error updating article.";
        }
        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
$conn->close();
?>
