<?php
function renderStudioCard($studio, $isAdmin = false) {
    // Проверить, существует ли изображение
    $imageExists = !empty($studio['image_path']) && file_exists($studio['image_path']);
    $imagePath = $imageExists ? htmlspecialchars($studio['image_path']) : null;

    echo '<a href="index.php?page=studio&studioId=' . htmlspecialchars($studio['id']) . '" class="studio-link" style="text-decoration: none; color: inherit;">';
    echo '<div class="studio-card" style="border: 1px solid #ccc; padding: 10px; border-radius: 5px; cursor: pointer;">';
    
    // Вывести изображение или заглушку
    if ($imagePath) {
        echo '<img src="' . $imagePath . '" alt="Studio Image" width="150" height="150" style="object-fit: cover; border-radius: 5px;">';
    } else {
        // SVG-заглушка
        echo '<div style="width: 260px; height: 220px; display: flex; align-items: center; justify-content: center; background-color: #f0f0f0; border-radius: 5px;">';
        echo '<svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
        echo '<path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20ZM12 7C10.9 7 10 7.9 10 9C10 10.1 10.9 11 12 11C13.1 11 14 10.1 14 9C14 7.9 13.1 7 12 7ZM11 13H13V17H11V13Z" fill="#888888"/>';
        echo '</svg>';
        echo '</div>';
    }

    // Другая информация о студии
    echo '<h3>' . htmlspecialchars($studio['name']) . '</h3>';
    echo '<p><strong>Type:</strong> ' . htmlspecialchars($studio['type']) . '</p>';
    echo '<p><strong>Description:</strong> ' . htmlspecialchars($studio['description']) . '</p>';

    // Проверка на наличие рейтинга
    if (isset($studio['average_rating']) && $studio['average_rating'] !== null) {
        echo '<p><strong>Rating:</strong> ' . htmlspecialchars($studio['average_rating']) . '</p>';
    } else {
        echo '<p><strong>Rating:</strong> No ratings yet</p>';
    }

    echo '</div>';
    echo '</a>';
}
?>
