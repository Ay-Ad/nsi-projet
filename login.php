<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Identifiants incorrects';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TechShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🛒 TechShop</h1>
            </div>
            <div class="nav-menu">
                <a href="index.php">Accueil</a>
            </div>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <h2>Connexion</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" name="username" required placeholder="ayoub">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required placeholder="ayb2008">
                </div>
                <button type="submit" class="btn-submit">Se connecter</button>
            </form>
            <p class="login-hint">💡 Identifiant: <strong>ayoub</strong> | Mot de passe: <strong>ayb2008</strong></p>

            <div style="text-align: center; margin-top: 1.5rem; color: #666;">
                Pas encore de compte ? <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: 600;">S'inscrire</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 TechShop. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>