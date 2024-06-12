<?php
declare(strict_types=1);
session_start();
include 'db.php';

class ArticleManager {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function sanitize_input(string $data): string {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    public function createArticle(string $title, string $content, ?int $category_id): bool {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("INSERT INTO article (title, content, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->bind_param("ss", $title, $content);
            if (!$stmt->execute()) {
                throw new Exception("Error creating article.");
            }
            $article_id = $stmt->insert_id;
            $stmt->close();

            if ($category_id) {
                $stmt = $this->conn->prepare("INSERT INTO article_has_category (article_id, category_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $article_id, $category_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error linking category.");
                }
                $stmt->close();
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getCategories(): array {
        $categories = [];
        $sql = "SELECT * FROM category";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = ['id' => $row['id'], 'name' => htmlspecialchars($row['name'])];
            }
        }
        return $categories;
    }
}

$articleManager = new ArticleManager($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $articleManager->sanitize_input($_POST['title']);
    $content = $articleManager->sanitize_input($_POST['content']);
    $category_id = !empty($_POST['category']) ? (int) $_POST['category'] : null;

    try {
        if (empty($title) || empty($content)) {
            $error_message = "Title and content are required.";
        } else {
            $articleManager->createArticle($title, $content, $category_id);
            $success_message = "Article created successfully!";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$categories = $articleManager->getCategories();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Article</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="create_article.php">Create Article</a></li>
            <li><a href="list_articles.php">List Articles</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Create Article</h1>
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php elseif (!empty($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <form action="create_article.php" method="post">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
            
            <label for="content">Content:</label>
            <textarea id="content" name="content" required></textarea>
            
            <label for="category">Category:</label>
            <select id="category" name="category">
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit">Save</button>
        </form>
    </div>
</body>
</html>
