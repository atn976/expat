<?php
include 'db.php';

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id > 0) {
    $sql = "SELECT * FROM article WHERE id = $article_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "Article non trouvé."]);
    }
} else {
    echo json_encode(["error" => "ID d'article invalide."]);
}

$conn->close();
?>
