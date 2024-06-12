<?php
declare(strict_types=1);
session_start();
include 'db.php';
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
                <?php
                $sql = "SELECT * FROM category";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                }
                ?>
            </select>
            <button type="submit">Filtrer</button>
        </form>
        <!-- Liste des articles -->
        <div id="articlesList">
        <?php
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
        $sql = "SELECT article.*, category.name AS category_name 
                FROM article 
                LEFT JOIN article_has_category ON article.id = article_has_category.article_id 
                LEFT JOIN category ON article_has_category.category_id = category.id";

        if ($category_id === 'uncategorized') {
            $sql .= " WHERE article_has_category.category_id IS NULL";
        } elseif ($category_id !== '') {
            $category_id = intval($category_id);
            $sql .= " WHERE article_has_category.category_id = $category_id";
        }

        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $category_name = $row['category_name'] ?? 'Sans catégorie';
                echo "<div class='article' data-id='" . $row['id'] . "'>";
                echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
                echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                echo "<p><strong>Catégorie:</strong> " . htmlspecialchars($category_name) . "</p>";
                echo "<p><strong>Date de création:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
                echo "<p><strong>Dernière modification:</strong> " . htmlspecialchars($row['updated_at']) . "</p>";
                echo "<button class='edit-article' data-id='" . $row['id'] . "'>Modifier</button>";
                echo "<button class='delete-article' data-id='" . $row['id'] . "'>Supprimer</button>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun article trouvé.</p>";
        }
        $conn->close();
        ?>
        </div>
    </div>
    <!-- Modal pour modifier l'article -->
    <div id="editModal">
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
            // Filtrer les articles par catégorie
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                var category_id = $('#filterCategory').val();
                window.location.href = 'list_articles.php?category_id=' + category_id;
            });

            // Ouvrir le modal pour éditer un article
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

            // Soumettre le formulaire d'édition
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

            // Annuler l'édition
            $('#cancelEdit').on('click', function() {
                $('#editModal').hide();
            });

            // Supprimer un article
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
