<?php
require_once __DIR__.'/../includes/db.php';
$num1 = random_int(0, 100);
$num2 = random_int(0, 100);

$action = $_GET['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $returnUrl = $_POST['returnUrl'] ?? $_GET['returnUrl'] ?? 'index.php?page=catalog';

    if ($action === 'login') {
        // CAPTCHA check
        
        
            // Login logic
            $query = $pdo->prepare("SELECT * FROM users WHERE email = ? AND isActive = 1");
            $query->execute([$email]);
            $user = $query->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

                $query = $pdo->prepare("INSERT INTO sessions (userId, token, expiresAt) VALUES (?, ?, ?)");
                $query->execute([$user['id'], $token, $expiresAt]);

                session_start();
                $_SESSION['token'] = $token;

                // Redirect to returnUrl
                header("Location: $returnUrl");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        
    } elseif ($action === 'register') {
        if ($_POST['captcha'] !== htmlspecialchars($num1 + $num2)) {
            $error = "Incorrect CAPTCHA answer.";
        } else {
        // Registration logic
        $username = $_POST['username'] ?? '';

        $query = $pdo->prepare("SELECT * FROM users WHERE email = ? AND isActive = 1");
        $query->execute([$email]);
        if ($query->fetch()) {
            $error = "Email is already in use.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = $pdo->prepare("INSERT INTO users (username, email, password, admin_rule, isActive) VALUES (?, ?, ?, 0, 1)");
            $query->execute([$username, $email, $hashedPassword]);

            $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $query->execute([$email]);
            $user = $query->fetch();

            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

            $query = $pdo->prepare("INSERT INTO sessions (userId, token, expiresAt) VALUES (?, ?, ?)");
            $query->execute([$user['id'], $token, $expiresAt]);

            session_start();
            $_SESSION['token'] = $token;

            header("Location: $returnUrl");
            exit;
        }
    }
    } elseif ($action === 'password-reset') {
        $email = $_POST['email'] ?? '';
        $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $query->execute([$email]);
        $user = $query->fetch();

        if ($user) {
            $success = "If this email is registered, you'll receive a password reset link.";
        } else {
            $error = "No account associated with this email.";
        }
    }
}

$returnUrl = htmlspecialchars($_GET['returnUrl'] ?? 'index.php?page=catalog');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'login' ? 'Login' : ($action === 'register' ? 'Register' : 'Password Reset') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9; }
        .auth-container h2 { text-align: center; }
        .auth-form input { width: calc(100% - 22px); margin: 10px 0; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .auth-form button { width: 100%; padding: 10px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .auth-form button:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        .social-login button { width: 48%; margin: 1%; padding: 10px; border: none; border-radius: 4px; cursor: pointer; }
        .social-login button:nth-child(1) { background: #db4437; color: #fff; }
        .social-login button:nth-child(2) { background: #4267b2; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h2><?= $action === 'login' ? 'Login' : ($action === 'register' ? 'Register' : 'Password Reset') ?></h2>

            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php elseif (isset($success)): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($action === 'login'): ?>
                <form class="auth-form" method="POST">
                    <input type="hidden" name="returnUrl" value="<?= $returnUrl ?>">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <div class="social-login">
                    <p>Or login with:</p>
                    <button onclick="alert('Google login coming soon!')">Google</button>
                    <button onclick="alert('Facebook login coming soon!')">Facebook</button>
                </div>
                <p>Don't have an account? <a href="?page=login&action=register&returnUrl=<?= urlencode($returnUrl) ?>">Register</a></p>
                <p><a href="?page=login&action=password-reset">Forgot your password?</a></p>

            <?php elseif ($action === 'register'): ?>
                <form class="auth-form" method="POST">
                    <input type="hidden" name="returnUrl" value="<?= $returnUrl ?>">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" oninput="checkPasswordStrength(this.value)" required>
                    <div id="password-strength" style="margin: 5px 0;">Password strength: <span>Weak</span></div>
                    <label for="captcha">What is <?= $num1 ?> + <?= $num2 ?>?</label>
                    <input type="text" name="captcha" id="captcha" placeholder="Answer" required>
                    <button type="submit">Register</button>
                </form>
                <p>Already have an account? <a href="?page=login&action=login&returnUrl=<?= urlencode($returnUrl) ?>">Login</a></p>
                <script>
                    function checkPasswordStrength(password) {
                        const strength = document.getElementById('password-strength').querySelector('span');
                        if (password.length < 6) {
                            strength.textContent = 'Weak';
                            strength.style.color = 'red';
                        } else if (password.length < 10) {
                            strength.textContent = 'Moderate';
                            strength.style.color = 'orange';
                        } else {
                            strength.textContent = 'Strong';
                            strength.style.color = 'green';
                        }
                    }
                </script>

            <?php elseif ($action === 'password-reset'): ?>
                <form method="POST">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit">Reset Password</button>
                </form>
                <p><a href="?page=login&action=login">Back to Login</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
