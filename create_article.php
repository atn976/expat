<?php
include 'db.php';

$sql = "SELECT * FROM category";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Article</title>
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
        <h1>Create an Article</h1>
        <form id="articleForm" method="post">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required><br><br>
            <label for="content">Content:</label>
            <textarea id="content" name="content" required></textarea><br><br>
            <label for="category">Category:</label>
            <select id="category" name="category">
                <option value="">None</option>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No categories found</option>";
                }
                ?>
            </select><br><br>
            <button type="submit">Submit</button>
        </form>
        <div id="message"></div>
    </div>

    <script>
        $(document).ready(function() {
            $('#articleForm').on('submit', function(e) {
                e.preventDefault();

                var title = $('#title').val();
                var content = $('#content').val();
                var category = $('#category').val();

                if (title === "" || content === "") {
                    $('#message').text("All fields except category are required.");
                    return;
                }

                $.ajax({
                    url: 'submit_article.php',
                    method: 'POST',
                    data: {
                        title: title,
                        content: content,
                        category: category
                    },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.success) {
                            window.location.href = res.redirect;
                        } else {
                            $('#message').text(res.message);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
