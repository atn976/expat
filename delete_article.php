<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
include 'db.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $article_id = (int) $_POST['id'];

    // Supprimer les associations de catégorie
    $stmt = $conn->prepare("DELETE FROM article_has_category WHERE article_id = ?");
    if ($stmt === false) {
        $response['success'] = false;
        $response['message'] = "Error preparing statement for deleting categories: " . $conn->error;
    } else {
        $stmt->bind_param("i", $article_id);
        if (!$stmt->execute()) {
            $response['success'] = false;
            $response['message'] = "Error deleting article categories: " . $stmt->error;
            $stmt->close();
            echo json_encode($response);
            $conn->close();
            exit();
        }
        $stmt->close();
    }

    // Supprimer l'article
    $stmt = $conn->prepare("DELETE FROM article WHERE id = ?");
    if ($stmt === false) {
        $response['success'] = false;
        $response['message'] = "Error preparing statement for deleting article: " . $conn->error;
    } else {
        $stmt->bind_param("i", $article_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = "Error deleting article: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request.";
}

echo json_encode($response);
$conn->close();
?>
