<?php
 
ob_start();
global $pdo;
$session = NULL;
$user = NULL;
$isAdmin = false;
$isOwnProfile = false;
function logout() {
    global $pdo;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_SESSION['token'])) {
        $query = $pdo->prepare("UPDATE sessions SET expiresAt = NOW() WHERE token = ?");
        $query->execute([$_SESSION['token']]);
    }
    session_destroy();
    header('Location: index.php?page=catalog');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    logout(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    global $pdo, $userId;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($userId) {
        $query = $pdo->prepare("UPDATE users SET isActive = 0 WHERE id = ?");
        $query->execute([$userId]);
        logout();
    } else {
        setMessage('error','Error: User not logged in.');
        header('Location: index.php?page=profile?section=delete');
        exit;
    }
}

if (isset($_SESSION['token'])) {
    global $pdo;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('SELECT * FROM sessions WHERE token = :token');
    $stmt->bindParam(':token',$_SESSION['token'], PDO::PARAM_STR);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
}
if($session) {
$userId = $session['userId'];
$query = $pdo->prepare("SELECT * FROM users WHERE id = ? AND isActive = 1");
$query->execute([$userId]);
$user = $query->fetch();
} else {
    header("Location: index.php?page=catalog");
}


function displayMessages() {
    if (!empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $msg) {
            echo '<div class="message ' . htmlspecialchars($msg['type']) . '">' . htmlspecialchars($msg['message']) . '</div>';
        }
        unset($_SESSION['flash_messages']);
    }
}
function setMessage($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
        
    }
    foreach ($_SESSION['flash_messages'] as $msg) {
        if ($msg['type'] === $type && $msg['message'] === $message) {
            return;
        }
    }

    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create-studio'])) {
    if (isset($session['userId'])) {
        $name = $_POST['studio-name'];
        $type = $_POST['studio-type'];
        $contacts = $_POST['studio-contacts'];
        $description = $_POST['studio-description'];
        $ownerId = $session['userId'];

        $imagePath = null;
        if (isset($_FILES['studio-image']) && $_FILES['studio-image']['error'] === 0) {
            $imageDir = 'uploads/studios/';
            $imageName = uniqid() . '_' . basename($_FILES['studio-image']['name']);
            $imagePath = $imageDir . $imageName;
            move_uploaded_file($_FILES['studio-image']['tmp_name'], $imagePath);
        }

        try {
            $query = $pdo->prepare("INSERT INTO studios (name, type, contacts, description, ownerId, image_path) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $query->execute([$name, $type, $contacts, $description, $ownerId, $imagePath]);
            header('Location: index.php?page=profile&studio_added=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                setMessage('error',"Error: Studio name already exists. Please choose a different name.");
            } else {
                setMessage('error',"An error occurred: " . $e->getMessage());
            }
            header('Location: index.php?page=profile');
            exit;
        }
    } else {
        setMessage('error','You need to log in to create a studio.');
        header('Location: index.php?page=profile');
        exit;
    }
}

if (isset($_GET['studio_added']) && $_GET['studio_added'] == 1) {
    setMessage('success',"Studio created successfully!");
}

$isLoggedIn = false;
if ($session) {
    $isLoggedIn = true;
}
$currentUserId = $isLoggedIn ? $session['userId'] : null;
if ($user) {
    $isAdmin = $user['isAdmin'];
}

$profileId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if (!$isLoggedIn && $profileId === null) {
    $isOwnProfile = false;
    $profileType = 'guest';
} elseif ($isLoggedIn && $profileId === null) {
    $isOwnProfile = true;
    $profileType = 'own';
    $profileId = $currentUserId;
} elseif ($profileId !== null) {
    if ($isLoggedIn && $profileId === $currentUserId) {
        $isOwnProfile = true;
        $profileType = 'own';
    } else {
        $isOwnProfile = false;
        $profileType = 'other';
    }
} else {
    $isOwnProfile = false;
    $profileType = 'unknown';
}

if($session) {
    $query = $pdo->prepare("SELECT * FROM users WHERE id = ? AND isActive = 1");
    $query->execute([$profileId]);
    $user = $query->fetch();
    }
    if($profileId) {
        $query = $pdo->prepare("SELECT * FROM users WHERE id = ? AND isActive = 1");
        $query->execute([$profileId]);
        $user = $query->fetch();
        }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_studio'])) {
    $studioId = $_POST['studio_id'];
    $query = $pdo->prepare("UPDATE `studios` SET `isActive` = 0 WHERE id = ?");
    $query->execute([$studioId, $userId]);
    header('Location: index.php?page=profile&studio_deleted=1');
    exit;
}


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit-profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
    
        $query = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $query->execute([$username, $email, $userId]);
    
        header('Location: index.php?page=profile&profile_updated=1');
        exit;
    }
    



// Основная функция для обработки POST-запросов
function handlePostRequest($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return; // Если метод не POST, ничего не делаем
    }
    global $user;
    $actions = [
        'toggle_user_status' => toggleUserStatus($pdo, $_POST['user_id'], $user['isActive'], "blocked"),
        'block_user' => fn() => toggleUserStatus($pdo, $_POST['user_id'], 0, "blocked"),
        'unblock_user' => fn() => toggleUserStatus($pdo, $_POST['user_id'], 1, "unblocked"),
        'block_user_name' => fn() => toggleUserStatusByField($pdo, 'username', $_POST['user_name'], 0, "blocked"),
        'unblock_user_name' => fn() => toggleUserStatusByField($pdo, 'username', $_POST['user_name'], 1, "unblocked"),
        'block_user_email' => fn() => toggleUserStatusByField($pdo, 'email', $_POST['user_email'], 0, "blocked"),
        'unblock_user_email' => fn() => toggleUserStatusByField($pdo, 'email', $_POST['user_email'], 1, "unblocked"),
        'block_user_name_email' => fn() => blockUserByNameEmail($pdo, $_POST['user_name'], $_POST['user_email']),
        'delete_studio_admin' => fn() => toggleStudioStatus($pdo, $_POST['studio_id'], 0, "deleted"),
        'undelete_studio_admin' => fn() => toggleStudioStatus($pdo, $_POST['studio_id'], 1, "restored"),
        'grant' => fn() => changeAdminRights($pdo, $_POST['user_id'], $_POST['action']),
        'revoke' => fn() => changeAdminRights($pdo, $_POST['user_id'], $_POST['action']),
    ];

    $actionKey = $_POST['action'] ?? null; // Получаем ключ действия из POST
    if ($actionKey && isset($actions[$actionKey])) {
        $actions[$actionKey](); // Вызываем соответствующую функцию
    } else {
        setMessage('error', 'Invalid action specified.');
        redirectToAdminPanel();
    }
}

// Функция для блокировки/разблокировки пользователя по ID
function toggleUserStatus($pdo, $userId, $status, $action) {
    global $user;

    if ($userId && $userId != $user['id']) {
        if (entityExists($pdo, 'users', 'id', $userId)) {
            updateEntity($pdo, 'users', 'isActive', $status, 'id', $userId);
            setMessage('success', "User ID {$userId} has been {$action}.");
        } else {
            setMessage('error', "User ID {$userId} does not exist.");
        }
    } else {
        setMessage('error', "You cannot {$action} yourself!");
    }
    redirectToAdminPanel();
}

// Функция для изменения статуса пользователя по любому полю (например, имени или email)
function toggleUserStatusByField($pdo, $field, $value, $status, $action) {
    if ($value) {
        updateEntity($pdo, 'users', 'isActive', $status, $field, $value);
        setMessage('success', "User with {$field} '{$value}' has been {$action}.");
    } else {
        setMessage('error', "Invalid {$field}.");
    }
    redirectToAdminPanel();
}

// Функция для блокировки пользователей по имени и email
function blockUserByNameEmail($pdo, $name, $email) {
    $query = "UPDATE users SET isActive = 0 WHERE 1=1";
    $params = [];
    if ($name) {
        $query .= " AND username = :name";
        $params[':name'] = $name;
    }
    if ($email) {
        $query .= " AND email = :email";
        $params[':email'] = $email;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    setMessage('success', "Users matching criteria have been blocked.");
    redirectToAdminPanel();
}

// Функция для изменения статуса студии (удаление/восстановление)
function toggleStudioStatus($pdo, $studioId, $status, $action) {
    if (entityExists($pdo, 'studios', 'id', $studioId)) {
        updateEntity($pdo, 'studios', 'isActive', $status, 'id', $studioId);
        setMessage('success', "Studio ID {$studioId} has been {$action}.");
    } else {
        setMessage('error', "Studio ID {$studioId} does not exist.");
    }
    redirectToAdminPanel();
}

// Функция для изменения прав администратора
function changeAdminRights($pdo, $userId, $action) {
    $newStatus = $action === 'grant' ? 1 : 0;

    if (entityExists($pdo, 'users', 'id', $userId)) {
        updateEntity($pdo, 'users', 'isAdmin', $newStatus, 'id', $userId);
        $message = $action === 'grant' ? 'granted' : 'revoked';
        setMessage('success', "Admin rights have been {$message} for User ID {$userId}.");
    } else {
        setMessage('error', "User ID {$userId} does not exist.");
    }
    redirectToAdminPanel();
}

// Проверяет существование сущности в таблице
function entityExists($pdo, $table, $field, $value) {
    $query = $pdo->prepare("SELECT id FROM {$table} WHERE {$field} = ?");
    $query->execute([$value]);
    return $query->fetch();
}

// Обновляет поле в таблице
function updateEntity($pdo, $table, $fieldToUpdate, $newValue, $conditionField, $conditionValue) {
    $query = $pdo->prepare("UPDATE {$table} SET {$fieldToUpdate} = ? WHERE {$conditionField} = ?");
    $query->execute([$newValue, $conditionValue]);
}

// Редирект на панель администратора
function redirectToAdminPanel() {
    header('Location: index.php?page=profile&section=admin-panel');
    exit;
}

// Вызов главной функции
handlePostRequest($pdo);



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?></title>
</head>
<body>
    <div class="container-prf">
        <div class="sidebar">
            <?php if ($isOwnProfile): ?>
                <button class="button" onclick="showSection('main-info')">Main Info</button>
                <button class="button" onclick="showSection('favorites')">Favorites</button>
                <button class="button" onclick="showSection('notifications')">Notifications</button>
                <button class="button" onclick="showSection('settings')">Settings</button>
                <?php if ($isAdmin): ?>
                    <button class="button" onclick="showSection('admin-panel')">Admin Panel</button>
                <?php endif; ?>
                <button class="button" onclick="showSection('my-studios')">My Studios</button>
                <button class="button" onclick="showSection('create-studio')">Create Studio</button>
                <button class="button" onclick="showSection('delete')">Delete Account</button>
                <button class="button" onclick="showSection('logout')">Logout</button>
            <?php else: ?>
                <button class="button" onclick="showSection('main-info')">Main Info</button>
                <button class="button" onclick="showSection('my-studios')">Studios</button>
            <?php endif; ?>
        </div>

    
        <div class="main-content">
        <div id="messages">
                <?php
                displayMessages(); ?>
            </div>
            <div id="main-info" class="section active">
            <h2>Main Info</h2>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Status:</strong> <?= $user['isActive'] == 1 ? 'Active' : 'Inactive' ?></p>
            <?php
            if ($user['isAdmin'] == 1) {
                echo '[admin]';
            } ?>
            <?php if ($isAdmin && !$isOwnProfile): ?>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
            <button type="submit" name="toggle_user_status" class="button">
                <?= $user['isActive'] ? 'Ban User' : 'Unban User' ?>
            </button>
        </form>
    <?php endif; ?>
        </div>

        <div id="logout" class="section">
        <?php if ($isOwnProfile):?>
            <form class="form-prf" method="POST">
                <button type="submit" name="logout" class="lo-but">Logout</button>
            </form>
            <?php endif; ?>
        </div>
        <div id="favorites" class="section">
    <h2>Favorites</h2>
    <?php
    global $pdo;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT studioId FROM favorites WHERE userId = :userId');
    $stmt->bindParam(':userId', $user['id'], PDO::PARAM_INT);
    $stmt->execute();
    $favoriteStudioIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($favoriteStudioIds)) {
        $inClause = implode(',', array_fill(0, count($favoriteStudioIds), '?'));
        $query = $pdo->prepare("SELECT * FROM studios WHERE id IN ($inClause)");
        $query->execute($favoriteStudioIds);
        $favoriteStudios = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($favoriteStudios as $studio) {
            renderStudioCard($studio, $isAdmin);
        }
    } else {
        echo '<p>You have no favorite studios.</p>';
    }
    ?>
</div>

            <div id="notifications" class="section">
                <h2>Notifications</h2>
                <p>Manage your notification preferences.</p>
            </div>

            <div id="settings" class="section">
    <h2>Profile Settings</h2>
    <p>Change your password, update your profile, or other settings.</p>

    <h3>Edit Profile</h3>
    <form method="POST">
        <label for="edit-username">Username:</label><br>
        <input type="text" id="edit-username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

        <label for="edit-email">Email:</label><br>
        <input type="email" id="edit-email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <button type="submit" name="edit-profile" class="button">Save Changes</button>
    </form>
</div>

            <div id="my-studios" class="section">
    <h2>Studios</h2>

    <?php
    global $pdo;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('SELECT * FROM sessions WHERE token = :token');
    $stmt->bindParam(':token', $_SESSION['token'], PDO::PARAM_STR);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profileId) {
        $userId = $profileId;
        $query = $pdo->prepare("SELECT * FROM studios WHERE ownerId = ? AND isActive = 1");
        $query->execute([$userId]);
        $studios = $query->fetchAll(PDO::FETCH_ASSOC);
        
        if ($studios) {
            echo '<ul>';
            foreach ($studios as $studio) {
                renderStudioCard($studio, $isAdmin);
            }
        } else {
            echo '<p>You have no studios.</p>';
        }
    } else {
        echo '<p>You need to log in to see your studios.</p>';
    }
    ?>
</div>
<div id="admin-panel" class="section">

<h2>Admin Panel</h2>

<h3>Manage Users</h3>

<!-- General User Management Form -->
<form method="POST">
    <fieldset>
        <legend>Manage User</legend>
        
        <!-- Action Selection -->
        <label for="user-action">Action:</label>
        <select id="user-action" name="action" required>
            <option value="block_user">Block by ID</option>
            <option value="unblock_user">Unblock by ID</option>
            <option value="block_user_name">Block by Name</option>
            <option value="unblock_user_name">Unblock by Name</option>
            <option value="block_user_email">Block by Email</option>
            <option value="unblock_user_email">Unblock by Email</option>
        </select>

        <!-- User ID Input -->
        <div id="user-id-input" class="input-group">
            <label for="user-id">User ID:</label>
            <input type="number" id="user-id" name="user_id">
        </div>

        <!-- User Name Input -->
        <div id="user-name-input" class="input-group" style="display: none;">
            <label for="user-name">User Name:</label>
            <input type="text" id="user-name" name="user_name">
        </div>

        <!-- User Email Input -->
        <div id="user-email-input" class="input-group" style="display: none;">
            <label for="user-email">User Email:</label>
            <input type="email" id="user-email" name="user_email">
        </div>

        <button type="submit" class="button">Submit</button>
    </fieldset>
</form>

<h3>Manage Studios</h3>

<!-- Studio Management -->
<form method="POST">
    <fieldset>
        <legend>Manage Studio</legend>

        <!-- Action Selection -->
        <label for="studio-action">Action:</label>
        <select id="studio-action" name="action" required>
            <option value="delete_studio_admin">Delete Studio</option>
            <option value="undelete_studio_admin">Restore Studio</option>
        </select>

        <!-- Studio ID Input -->
        <label for="studio-id">Studio ID:</label>
        <input type="number" id="studio-id" name="studio_id" required>

        <button type="submit" class="button">Submit</button>
    </fieldset>
</form>

<h3>Admin Rights</h3>

<!-- Admin Rights Management -->
<form method="POST">
    <fieldset>
        <legend>Change Admin Rights</legend>

        <label for="admin-user-id">User ID:</label>
        <input type="number" id="admin-user-id" name="user_id" required>

        <label for="admin-action">Action:</label>
        <select id="admin-action" name="action" required>
            <option value="grant">Grant Admin Rights</option>
            <option value="revoke">Revoke Admin Rights</option>
        </select>

        <button type="submit" class="button">Submit</button>
    </fieldset>
</form>

<script>
    // Dynamically show/hide input fields based on selected action
    document.getElementById('user-action').addEventListener('change', function () {
        const action = this.value;
        document.getElementById('user-id-input').style.display = 
            action.includes('id') ? 'block' : 'none';
        document.getElementById('user-name-input').style.display = 
            action.includes('name') ? 'block' : 'none';
        document.getElementById('user-email-input').style.display = 
            action.includes('email') ? 'block' : 'none';
    });
</script>

</div>

<div id="create-studio" class="section">

    <h2>Create Studio</h2>
    <p>Fill in the details to create a new studio.</p>
    <form class="form-prf" action="" method="POST" enctype="multipart/form-data">
        <label for="studio-name">Studio Name:</label><br>
        <input type="text" id="studio-name" name="studio-name" required><br><br>

        <label for="studio-type">Studio Type:</label><br>
        <input type="text" id="studio-type" name="studio-type" required><br><br>

        <label for="studio-contacts">Contacts:</label><br>
        <input type="text" id="studio-contacts" name="studio-contacts" required><br><br>

        <label for="studio-description">Description:</label><br>
        <textarea id="studio-description" name="studio-description" required></textarea><br><br>

        <label for="studio-image">Image:</label><br>
        <input type="file" id="studio-image" name="studio-image" accept="image/*"><br><br>

        <button type="submit" name="create-studio" class="button">Create Studio</button>
    </form>
</div>

            <div id="delete" class="section">

    <h2>Delete Account</h2>
    <p>Are you sure you want to delete your account?</p>
    <form class="form-prf" method="POST">
        <button type="submit" name="delete_account" class="button">Yes, delete my account</button>
    </form>
</div>

        </div>
    </div>

    <script>
function showSection(sectionId) {
    var sections = document.querySelectorAll('.section');
    sections.forEach(function(section) {
        section.classList.remove('active');
    });

    var activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.add('active');
    }

    var url = new URL(window.location.href);
    url.searchParams.set('section', sectionId);
    history.pushState({}, '', url);
}
window.addEventListener('load', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var section = urlParams.get('section');
    if (section) {
        showSection(section);
    } else {
        showSection('main-info');
    }
});

window.addEventListener('popstate', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var section = urlParams.get('section');
    showSection(section || 'main-info');
});
    </script>
</body>
</html>
