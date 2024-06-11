<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
include 'db.php';

class Database {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function close() {
        $this->conn->close();
    }
}

class ArticleManager {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function sanitizeInput(string $data): string {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    public function createArticle(string $title, string $content, ?int $category_id): void {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("INSERT INTO article (title, content, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->bind_param("ss", $title, $content);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting article.");
            }

            $article_id = $stmt->insert_id;
            $stmt->close();

            if ($category_id) {
                $stmt = $this->db->prepare("INSERT INTO article_has_category (article_id, category_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $article_id, $category_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error linking category.");
                }
                $stmt->close();
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}

$response = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = new Database($conn);
        $articleManager = new ArticleManager($db);

        if (!isset($_POST['title']) || !isset($_POST['content'])) {
            throw new Exception("All fields (title and content) are required.");
        }

        $title = $articleManager->sanitizeInput($_POST['title']);
        $content = $articleManager->sanitizeInput($_POST['content']);
        $category_id = isset($_POST['category']) ? (int) $_POST['category'] : null;

        if (empty($title) || empty($content)) {
            throw new Exception("Title and content cannot be empty.");
        }

        $articleManager->createArticle($title, $content, $category_id);

        $response['success'] = true;
        $response['redirect'] = $category_id ? "list_articles.php?category_id=$category_id" : "list_articles.php";
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
