<?php
$returnUrl = $_POST['returnUrl'] ?? $_GET['returnUrl'] ?? 'index.php?page=catalog';

require_once __DIR__ . '/includes/db.php';

function handleRouting($page, $userId)
{
    switch ($page) {
        case 'login':
            require 'pages/login.php';
            break;
        case 'profile':
            require 'pages/profile.php';
            break;
        case 'catalog':
            require 'pages/catalog.php';
            break;
            case 'studio':
                require 'pages/studio.php';
                break;
        default:
            require 'pages/catalog.php';
            break;
    }
}
?>