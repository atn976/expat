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

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function close() {
        $this->conn->close();
    }
}

class CategoryRepository {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $categories = [];
        $result = $this->db->query("SELECT * FROM category");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = ['id' => $row['id'], 'name' => htmlspecialchars($row['name'])];
            }
        }
        return $categories;
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

        if ($category_id === -1) {
            $sql .= " WHERE article_has_category.category_id IS NULL";
        } elseif ($category_id > 0) {
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
$categoryRepository = new CategoryRepository($db);
$articleRepository = new ArticleRepository($db);

$categories = $categoryRepository->getAll();
$category_id = isset($_GET['category_id']) ? ($_GET['category_id'] === 'uncategorized' ? -1 : intval($_GET['category_id'])) : 0;
$articles = $articleRepository->getArticles($category_id);

$db->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Articles</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <!-- Formulaire de filtre -->
        <form id="filterForm">
            <label for="filterCategory">Filtrer par catégorie :</label>
            <select id="filterCategory" name="filterCategory">
                <option value="">Toutes</option>
                <option value="uncategorized">Sans catégorie</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrer</button>
        </form>
        <!-- Liste des articles -->
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
    <!-- Modal pour modifier l'article -->
    <div id="editModal" style="display:none;">
        <h2>Modifier l'article</h2>
        <form id="editArticleForm">
            <input type="hidden" id="editArticleId" name="id">
            <label for="editTitle">Titre :</label>
            <input type="text" id="editTitle" name="title" required>
            <label for="editContent">Contenu :</label>
            <textarea id="editContent" name="content" required></textarea>
            <button type="submit">Enregistrer</button>
            <button type="button" id="cancelEdit">Annuler</button>
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                var category_id = $('#filterCategory').val();
                window.location.href = 'list_articles.php?category_id=' + category_id;
            });

            $(document).on('click', '.edit-article', function() {
                var articleId = $(this).data('id');
                $.ajax({
                    url: 'get_article.php',
                    method: 'GET',
                    data: { id: articleId },
                    success: function(response) {
                        if (typeof response === 'string') {
                            try {
                                response = JSON.parse(response);
                            } catch (e) {
                                console.error("Erreur de parsing JSON:", e, response);
                                alert("Erreur de parsing JSON.");
                                return;
                            }
                        }
                        if (response.error) {
                            alert(response.error);
                        } else {
                            $('#editArticleId').val(response.id);
                            $('#editTitle').val(response.title);
                            $('#editContent').val(response.content);
                            $('#editModal').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de la récupération de l'article:", error);
                    }
                });
            });

            $('#editArticleForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_article.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (typeof response === 'string') {
                            try {
                                response = JSON.parse(response);
                            } catch (e) {
                                console.error("Erreur de parsing JSON:", e, response);
                                alert("Erreur de parsing JSON.");
                                return;
                            }
                        }
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("Erreur lors de la mise à jour de l'article: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur lors de la mise à jour de l'article:", error);
                    }
                });
            });

            $('#cancelEdit').on('click', function() {
                $('#editModal').hide();
            });

            $(document).on('click', '.delete-article', function() {
                var articleId = $(this).data('id');
                var confirmation = confirm("Êtes-vous sûr de vouloir supprimer cet article ?");
                if (confirmation) {
                    $.ajax({
                        url: 'delete_article.php',
                        method: 'POST',
                        data: { id: articleId },
                        success: function(response) {
                            if (typeof response === 'string') {
                                try {
                                    response = JSON.parse(response);
                                } catch (e) {
                                    console.error("Erreur de parsing JSON:", e, response);
                                    alert("Erreur de parsing JSON.");
                                    return;
                                }
                            }
                            if (response.success) {
                                location.reload();
                            } else {
                                alert("Erreur lors de la suppression de l'article: " + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Erreur lors de la suppression de l'article:", error);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
