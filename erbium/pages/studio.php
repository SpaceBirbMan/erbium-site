<?php
$user = NULL;
global $studio;
if (isset($_SESSION['token'])) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM sessions WHERE token = :token');
    $stmt->bindParam(':token',$_SESSION['token'], PDO::PARAM_STR);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    $userId = $session['userId'];
    $query = $pdo->prepare("SELECT * FROM users WHERE id = ? AND isActive = 1");
$query->execute([$userId]);
$user = $query->fetch();
}

$logged = false;
if ($user) {
    $logged = true;
}

if (!isset($_GET['studioId'])) {
    header('Location: index.php?page=catalog');
    exit;
}

global $studioId;
$studioId = intval($_GET['studioId']);
global $pdo;
$query = $pdo->prepare("SELECT * FROM studios WHERE id = ?");
$query->execute([$studioId]);
$studio = $query->fetch(PDO::FETCH_ASSOC);

if (!$studio) {
    echo "Studio not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report'])) {
    // $reviewId = intval($_POST['reviewId']);
    // $message = "Complaint about review ID: $reviewId";

    // $stmt = $pdo->prepare("INSERT INTO messages (senderId, receiverId, message, createdAt) VALUES (?, ?, ?, NOW())");
    // $stmt->execute([$user['id'], $adminId, $message]);
    // echo "Complaint submitted.";
}
// Check if the form was submitted to delete a review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    // Get the review_id and studioId from the POST data
    $reviewId = (int)$_POST['review_id'];
    $studioId = (int)$_GET['studioId'];

    // Validate if the review exists
    $query = $pdo->prepare("SELECT id FROM reviews WHERE isActive = 1 AND id = ?");
    $query->execute([$reviewId]);
    $review = $query->fetch();

    if ($review) {
        $updateQuery = $pdo->prepare("UPDATE reviews SET isActive = 0 WHERE id = ?");
        $updateQuery->execute([$reviewId]);
        header("Location: index.php?page=studio&studioId={$studioId}");
        exit;
    } else {
        header("Location: index.php?page=studio&studioId={$studioId}");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review'])) {
    $reviewId = $_POST['review_id'];
    $newComment = $_POST['new_comment'];
    $newRating = intval($_POST['new_rating']);

    if ($newRating < 1 || $newRating > 5) {
        echo "Rating must be between 1 and 5.";
        exit;
    }

    $query = $pdo->prepare("SELECT id FROM reviews WHERE id = ?");
    $query->execute([$reviewId]);
    $review = $query->fetch();

    if ($review) {
        $updateQuery = $pdo->prepare("UPDATE reviews SET comment = ?, rating = ? WHERE id = ?");
        $updateQuery->execute([$newComment, $newRating, $reviewId]);
        echo "Review ID {$reviewId} has been updated.";
    } else {
        echo "Review ID {$reviewId} does not exist.";
    }

    header("Location: index.php?page=studio&studioId=$studioId");
    exit;
}

$isOwner = false;
if ($logged && $studio && $studio['ownerId'] == $user['id']) {
    $isOwner = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_project') {
    if (!$isOwner) {
        echo "Access denied.";
        exit;
    }

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name && $description) {
        $stmt = $pdo->prepare("INSERT INTO studioProjects (studioId, name, description, createdAt) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$studioId, $name, $description]);

        header("Location: index.php?page=studio&studioId=$studioId");
        exit;
    } else {
        echo "All fields must be filled.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_field') {
    if (!$isOwner) {
        echo "Access denied.";
        exit;
    }

    $field = $_POST['field'];
    $value = trim($_POST['value']);
    $allowedFields = ['name', 'description', 'contacts', 'type'];

    if (!in_array($field, $allowedFields)) {
        echo "Incorrect field.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE studios SET $field = ? WHERE id = ?");
    $stmt->execute([$value, $studioId]);
    header("Location: index.php?page=studio&studioId=$studioId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($studio['name']) ?></title>
</head>
<body>
    <div class="std">
    <div class="studio-info">
        <?php
        $query = $pdo->prepare("SELECT * FROM users WHERE id = ? AND isActive = 1");
        $query->execute([$studio['ownerId']]);
        $userForStudio = $query->fetch(); 
        ?>
        <h1><?= htmlspecialchars($studio['name']) ?></h1>
        <img src="<?= htmlspecialchars($studio['image_path']) ?>" alt="<?= htmlspecialchars($studio['name']) ?>">
        <p><strong>Owner:</strong> <?= htmlspecialchars($userForStudio['username']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($studio['type']) ?></p>
        <p><strong>Contacts:</strong> <?= htmlspecialchars($studio['contacts']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($studio['description']) ?></p>
        <p><strong>Average Rating:</strong> <?= htmlspecialchars($studio['average_rating']) ?? 'No ratings yet' ?></p>
        <?php 
        if ($user) {
        if ($user['isAdmin'] == 1) {
            echo "<p>OwnerId: " . htmlspecialchars($studio['ownerId']) ."</p>";
            echo "<p>StudioId: " . htmlspecialchars($studio['id']) . "</p>";
        }
    }
        ?>
    </div>
    <?php 
    $query = $pdo->prepare("
    SELECT 
        studioProjects.id AS projectId,
        studioProjects.name AS projectName,
        studioProjects.description AS projectDescription,
        studioProjects.createdAt AS projectCreatedAt
    FROM studioProjects
    JOIN studios ON studioProjects.studioId = studios.id
    WHERE studios.id = ?
");
if ($isOwner): ?>
<div class="add-proj">
    <button onclick="document.getElementById('add-project-form').style.display = 'block';">Add project</button>
    <form id="add-project-form" style="display: none;">
    <label for="name">Project name:</label>
    <input type="text" id="name" name="name" required>
    <br>
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>
    <br>
    <button type="button" id="save-project-button">Save</button>
</form>

    </div>
<?php endif;

$query->execute([$studioId]);
$projects = $query->fetchAll(PDO::FETCH_ASSOC);
renderProjectsCards($projects);

    ?>
</div>
    <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-to-favorites'])) {
    if ($userId) {
        $query = $pdo->prepare("INSERT IGNORE INTO favorites (userId, studioId) VALUES (?, ?)");
        $query->execute([$userId, $studioId]);
        echo "Studio added to favorites.";
    } else {
        echo "You need to log in to add to favorites.";
    }
}
?>
<?php if ($user): ?>
<form method="POST">
    <button class="add-to-rew" type="submit" name="add-to-favorites">Add to Favorites</button>
</form>
<?php endif; ?>
<?php if($user):?>
    <div class="review-form">
        <h2>Leave a Review</h2>
        <form method="POST">
            <textarea name="review" placeholder="Your review" required></textarea><br>
            <label for="rating">Rating:</label>
            <select name="rating" id="rating" required>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select><br>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-review'])) {
    $review = $_POST['review'];
    $rating = intval($_POST['rating']); 
    if ($rating < 1 || $rating > 5) {
        echo "Rating must be between 1 and 5.";
        exit;
    }

    if ($userId) {
        $review = $_POST['review'] ?? null; // Разрешаем пустое значение
$rating = intval($_POST['rating']);

$query = $pdo->prepare("INSERT INTO reviews (studio_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$query->execute([$studioId, $userId, $rating, $review ?: null]); // Вставляем NULL вместо пустого комментария


        $updateRatingQuery = $pdo->prepare("
            UPDATE studios 
            SET average_rating = (SELECT AVG(rating) FROM reviews WHERE studio_id = ?) 
            WHERE id = ?
        ");
        $updateRatingQuery->execute([$studioId, $studioId]);
        header("Location: index.php?page=studio&studioId=$studioId" . $_SERVER['GET']);
        echo "Review submitted successfully.";
    } else {
        echo "You need to log in to leave a review.";
    }
}
?>
            <button type="submit" name="submit-review">Submit Review</button>
        </form>

    </div>   
    <?php endif; ?>
    <div class="reviews">
    <h2>Reviews</h2>
    <?php
    $sortField = 'created_at';
    $sortOrder = 'DESC';
    if (isset($_GET['sort'])) {
        $sortField = $_GET['sort'] ?? 'created_at'; // По умолчанию сортировка по дате
    }
    if (isset($_GET['order'])) {
        $sortOrder = $_GET['order'] === 'asc' ? 'ASC' : 'DESC'; // По умолчанию убывание
    }
        $allowedFields = ['created_at', 'rating']; // Допустимые поля сортировки
        
        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'created_at'; // Защита от SQL-инъекций
        }
        
        $reviewsQuery = $pdo->prepare("
            SELECT reviews.*, users.username, users.avatar
            FROM reviews
            JOIN users ON reviews.user_id = users.id
            WHERE reviews.studio_id = ? AND reviews.isActive = 1
            ORDER BY $sortField $sortOrder
        ");
        $reviewsQuery->execute([$studioId]);
        $reviews = $reviewsQuery->fetchAll(PDO::FETCH_ASSOC);
        
        ?>
        <form method="GET" action="">
    <input type="hidden" name="page" value="studio">
    <input type="hidden" name="studioId" value="<?= htmlspecialchars($studioId) ?>">
    <label for="sort">Sort by:</label>
    <select name="sort" id="sort">
        <option value="created_at" <?= ($_GET['sort'] ?? '') === 'created_at' ? 'selected' : '' ?>>Date</option>
        <option value="rating" <?= ($_GET['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Rating</option>
    </select>
    <select name="order" id="order">
        <option value="desc" <?= ($_GET['order'] ?? '') === 'desc' ? 'selected' : '' ?>>Descending</option>
        <option value="asc" <?= ($_GET['order'] ?? '') === 'asc' ? 'selected' : '' ?>>Ascending</option>
    </select>
    <button type="submit">Sort</button>
</form>

        <?php
        foreach ($reviews as $review) {
            echo "<div class='review-div'>";
            if (!empty($review['avatar'])) {
                echo "<a href='profile.php?id=" . htmlspecialchars($review['user_id']) . "'>";
                echo "<img class='avatar' src='" . htmlspecialchars($review['avatar']) . "' alt='Avatar'>";
                echo "</a>";
            } else {
                $initials = strtoupper($review['username'][0]);
                echo "<a href='index.php?page=profile&user_id=" . htmlspecialchars($review['user_id']) . "'>";
                echo "<div class='avatar-placeholder'>$initials</div>";
                echo "</a>";
            }
            echo "<div class='review-content'>";
            echo "<p><strong><a href='index.php?page=profile&user_id=" . htmlspecialchars($review['user_id']) . "'>" . htmlspecialchars($review['username']) . "</a></strong></p>";
            echo "<p><strong>Rating:</strong> " . htmlspecialchars($review['rating']) . " / 5</p>";
            echo '<p >' . ($review['comment'] ? htmlspecialchars($review['comment']) : "[No comment provided]") . "</p>";
            echo "<p class='review-date'>" . htmlspecialchars($review['created_at']) . "</p>";
        
            if ($logged) {
                echo "<div class='review-actions'>";
                if ($review['user_id'] === $user['id']) {
                    echo "<button onclick=\"toggleForm('edit', {$review['id']})\">Edit</button>";
                    echo "<form id='edit-form-{$review['id']}' style='display: none;' method='POST'>
                        <input type='hidden' name='edit_review' value='1'>
                        <input type='hidden' name='review_id' value='" . htmlspecialchars($review['id']) . "'>
                        <textarea name='new_comment' placeholder='Enter new comment'></textarea>
                        <input type='number' name='new_rating' min='1' max='5' placeholder='Rating (1-5)' required>
                        <button type='submit'>Save</button>
                    </form>";
                    echo "<form action='index.php?page=studio&studioId=" . (int)$studioId . "' method='POST'>";
echo "<input type='hidden' name='action' value='delete_review'>";
echo "<input type='hidden' name='review_id' value='" . htmlspecialchars($review['id']) . "'>";
echo "<button type='submit'>Delete</button>";
echo "</form>";
                } elseif ($user['isAdmin'] == 1) {
                    echo "<form action='index.php?page=studio&studioId=" . (int)$studioId . "' method='POST'>";
echo "<input type='hidden' name='action' value='delete_review'>";
echo "<input type='hidden' name='review_id' value='" . htmlspecialchars($review['id']) . "'>";
echo "<button type='submit'>Delete</button>";
echo "</form>";
                }
                echo "<button onclick=\"toggleForm('report', {$review['id']})\">Report</button>";
                echo "<form id='report-form-{$review['id']}' style='display: none;' method='POST'>
                    <input type='hidden' name='report' value='1'>
                    <input type='hidden' name='reviewId' value='" . htmlspecialchars($review['id']) . "'>
                    <textarea name='report_reason' placeholder='Reason for reporting' required></textarea>
                    <button type='submit'>Submit Report</button>
                </form>";
                echo "</div>";
            }
        
            echo "</div>";
            echo "</div><hr>";
        }
        
?>   
<?php if ($isOwner): ?>
    <h1>
        <?= htmlspecialchars($studio['name']) ?>
        <button onclick="toggleEdit('name')">✏️</button>
    </h1>
    <form id="edit-name-form" style="display:none;" method="POST">
        <input type="hidden" name="action" value="edit_field">
        <input type="hidden" name="field" value="name">
        <input type="text" name="value" value="<?= htmlspecialchars($studio['name']) ?>" required>
        <button type="submit">Save</button>
    </form>
    <p>
        <strong>Description:</strong> <?= htmlspecialchars($studio['description']) ?>
        <button onclick="toggleEdit('description')">✏️</button>
    </p>
    <form id="edit-description-form" style="display:none;" method="POST">
        <input type="hidden" name="action" value="edit_field">
        <input type="hidden" name="field" value="description">
        <textarea name="value" required><?= htmlspecialchars($studio['description']) ?></textarea>
        <button type="submit">Save</button>
    </form>
<?php endif; ?>

</div>
</body>
<script>
    function toggleForm(action, reviewId) {
        const formId = `${action}-form-${reviewId}`;
        const form = document.getElementById(formId);
        if (form) {
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    }
    function toggleEdit(field) {
    const form = document.getElementById(`edit-${field}-form`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

</html>