<?php
include 'db.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
$content = isset($_POST['content']) ? $conn->real_escape_string($_POST['content']) : '';

if ($id > 0 && !empty($title) && !empty($content)) {
    $sql = "UPDATE article SET title='$title', content='$content' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour de l'article: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Données invalides fournies."]);
}

$conn->close();
?>
