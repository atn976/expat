<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Articles</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav>
        <ul>
            <li><a href="create_article.php">Create Article</a></li>
            <li><a href="list_articles.php">List Articles</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Articles</h1>
        <form id="filterForm">
            <label for="filterCategory">Filter by Category:</label>
            <select id="filterCategory" name="filterCategory">
                <option value="">All</option>
                <?php
                include 'db.php';
                $sql = "SELECT * FROM category";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                }
                ?>
            </select>
            <button type="submit">Filter</button>
        </form>
        <div id="articlesList"></div>
    </div>

    <script>
        $(document).ready(function() {
            loadArticles();

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadArticles();
            });

            function loadArticles() {
                var category_id = $('#filterCategory').val();
                $.ajax({
                    url: 'fetch_articles.php',
                    method: 'GET',
                    data: { category_id: category_id },
                    success: function(response) {
                        $('#articlesList').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#articlesList').html("<p>Error loading articles.</p>");
                    }
                });
            }
        });
    </script>
</body>
</html>
