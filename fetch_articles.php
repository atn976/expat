<?php
declare(strict_types=1);
session_start();
include 'db.php';

class Database {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function close() {
        $this->conn->close();
    }
}

class ArticleRepository {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getArticles(int $category_id = 0): array {
        $sql = "SELECT article.*, category.name AS category_name 
                FROM article 
                LEFT JOIN article_has_category ON article.id = article_has_category.article_id 
                LEFT JOIN category ON article_has_category.category_id = category.id";
        $params = [];

        if ($category_id > 0) {
            $sql .= " WHERE article_has_category.category_id = ?";
            $params[] = $category_id;
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param("i", ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $articles = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $articles[] = [
                    'id' => $row['id'],
                    'title' => htmlspecialchars($row['title']),
                    'content' => htmlspecialchars($row['content']),
                    'category_name' => htmlspecialchars($row['category_name'] ?? 'Sans catégorie'),
                    'created_at' => htmlspecialchars($row['created_at']),
                    'updated_at' => htmlspecialchars($row['updated_at'])
                ];
            }
        }

        $stmt->close();
        return $articles;
    }
}

$db = new Database($conn);
$articleRepository = new ArticleRepository($db);

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$articles = $articleRepository->getArticles($category_id);

$db->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des articles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="create_article.php">Créer un article</a></li>
            <li><a href="list_articles.php">Liste des articles</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Articles</h1>
        <div id="articlesList">
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                    <div class='article' data-id='<?php echo $article['id']; ?>'>
                        <h2><?php echo $article['title']; ?></h2>
                        <p><?php echo $article['content']; ?></p>
                        <p><strong>Catégorie:</strong> <?php echo $article['category_name']; ?></p>
                        <p><strong>Date de création:</strong> <?php echo $article['created_at']; ?></p>
                        <p><strong>Dernière modification:</strong> <?php echo $article['updated_at']; ?></p>
                        <button class='edit-article' data-id='<?php echo $article['id']; ?>'>Modifier</button>
                        <button class='delete-article' data-id='<?php echo $article['id']; ?>'>Supprimer</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun article trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
