<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/studioCard.php';
require_once 'includes/projects.php';
// require_once 'ajax/add_to_favorites.php';
// require_once 'ajax/sort_reviews.php';
require_once 'router.php';

function getCurrentUserId() {
    global $pdo;
    if (!isset($_SESSION['token'])) {
        return null;
    }
    $query = $pdo->prepare("SELECT userId FROM sessions WHERE token = ? AND expiresAt > NOW()");
    $query->execute([$_SESSION['token']]);
    $session = $query->fetch(PDO::FETCH_ASSOC);
    return $session ? $session['userId'] : null;
}

$userId = getCurrentUserId();

?>
<header>
    <div class="header-container">
        <a href="index.php?page=catalog" class="header-logo">Erbium</a>
        <nav class="header-nav">
            <a href="index.php?page=catalog" class="header-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span>Home</span>
            </a>
            <?php if ($userId): ?>
                <div class="header-btn notifications-btn" onclick="toggleNotifications()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                    </svg>
                    <span>Notifications</span>
                    <div class="notifications-dropdown" id="notifications-dropdown">
                        <p>Empty yet.</p>
                    </div>
                </div>
                <a href="index.php?page=profile" class="header-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                        <path d="M16 13v-2H8v2H5v6h14v-6h-3zm-6-7h2V3h-2v3zm6 0h2V3h-2v3zm-6 0h2V3h-2v3z"/>
                    </svg>
                    <span>Profile</span>
                </a>
            <?php else: ?>
                <a href="index.php?page=login" class="header-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                    </svg>
                    <span>Login</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php
$page = $_GET['page'] ?? 'catalog';
handleRouting($page, $userId);
?>

<footer>
    <div class="footer-container">
        <p>&copy; 2024 NebulaDigital. All rights reserved.</p>
    </div>
</footer>

<link rel="stylesheet" href="//localhost/Erbium/assets/css/styles.css">
<link rel="stylesheet" href="//localhost/Erbium/assets/css/fonts.css">
<link rel="stylesheet" href="//localhost/Erbium/assets/css/root.css">
<link rel="stylesheet" href="//localhost/Erbium/assets/css/media.css">

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Перехват формы добавления проекта
    const addProjectForm = document.getElementById("add-project-form");
    if (addProjectForm) {
        addProjectForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const formData = new FormData(addProjectForm);
            try {
                const response = await fetch(addProjectForm.action || location.href, {
                    method: "POST",
                    body: formData,
                });
                if (response.ok) {
                    const result = await response.text();
                    document.querySelector(".std").innerHTML = result; // Обновление контента
                } else {
                    console.error("Ошибка при добавлении проекта");
                }
            } catch (error) {
                console.error("Ошибка сети:", error);
            }
        });
    }

    // Перехват формы добавления в избранное
    const favoriteButton = document.querySelector(".add-to-rew button[name='add-to-favorites']");
    if (favoriteButton) {
        favoriteButton.addEventListener("click", async (event) => {
            event.preventDefault();
            const form = favoriteButton.closest("form");
            const formData = new FormData(form);
            try {
                const response = await fetch(form.action || location.href, {
                    method: "POST",
                    body: formData,
                });
                if (response.ok) {
                    const result = await response.text();
                    alert("Добавлено в избранное");
                } else {
                    console.error("Ошибка при добавлении в избранное");
                }
            } catch (error) {
                console.error("Ошибка сети:", error);
            }
        });
    }

    // Перехват формы оставления отзыва
    const reviewForm = document.querySelector(".review-form form");
    if (reviewForm) {
        reviewForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const formData = new FormData(reviewForm);
            try {
                const response = await fetch(reviewForm.action || location.href, {
                    method: "POST",
                    body: formData,
                });
                if (response.ok) {
                    const result = await response.text();
                    document.querySelector(".reviews").innerHTML = result; // Обновление отзывов
                } else {
                    console.error("Ошибка при добавлении отзыва");
                }
            } catch (error) {
                console.error("Ошибка сети:", error);
            }
        });
    }

    // Перехват сортировки отзывов
    const sortForm = document.querySelector("form[action='']");
    if (sortForm) {
        sortForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const formData = new FormData(sortForm);
            const queryParams = new URLSearchParams(formData).toString();
            try {
                const response = await fetch(`${location.pathname}?${queryParams}`);
                if (response.ok) {
                    const result = await response.text();
                    document.querySelector(".reviews").innerHTML = result; // Обновление отзывов
                } else {
                    console.error("Ошибка при сортировке отзывов");
                }
            } catch (error) {
                console.error("Ошибка сети:", error);
            }
        });
    }
});
</script>
