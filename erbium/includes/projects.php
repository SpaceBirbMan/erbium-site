<?php
function renderProjectsCards($projects) {
    // Стиль контейнера для карточек
    echo '<div class= "proj-cards" style="display: flex; flex-wrap: wrap; gap: 20px;">';

    // Проверка на наличие проектов
    if (!empty($projects)) {
        foreach ($projects as $project) {
            echo '<div style="
                border: 1px solid #ccc; 
                border-radius: 10px; 
                padding: 15px; 
                width: calc(33.333% - 20px); 
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
                background-color: #fff;
            ">';
            echo '<h3 style="margin: 0 0 10px;">' . htmlspecialchars($project['projectName']) . '</h3>';
            echo '<p><strong>Description:</strong> ' . htmlspecialchars($project['projectDescription']) . '</p>';
            echo '<p><strong>Created At:</strong> ' . htmlspecialchars($project['projectCreatedAt']) . '</p>';
            // echo var_dump($project);
            echo '<a 
                style="display: inline-block; margin-top: 10px; text-decoration: none; 
                color: #fff; background-color: #007BFF; padding: 10px 15px; border-radius: 5px;">
                View Details
            </a>';
            echo '</div>';
        }
    } else {
        // Если проектов нет
        echo '<p style="width: 100%; text-align: center; font-size: 18px;">No projects available</p>';
    }

    echo '</div>';
}
?>
