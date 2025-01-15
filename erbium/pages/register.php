<?php
require_once __DIR__.'/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = $db->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    if ($query->fetch()) {
        $error = "Email is already in use.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $query = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 0)");
        $query->execute([$username, $email, $hashedPassword]);

        $success = "Registration successful. Please log in.";
    }
}
$returnUrl = htmlspecialchars($_GET['returnUrl'] ?? 'index.php?page=catalog');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication</title>
</head>
<body>
    <div class="auth-container">
        <h2>Authentication</h2>
        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php elseif (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="register">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>