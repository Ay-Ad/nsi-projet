<?php
require_once 'config.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation des champs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif (strlen($username) < 3) {
        $error = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error = 'Ce nom d\'utilisateur ou cet email existe déjà';
        } else {
            // Crypter le mot de passe avec bcrypt
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insérer le nouvel utilisateur
            try {
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword]);

                $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';

                // Redirection automatique après 2 secondes
                header("refresh:2;url=login.php");
            } catch (PDOException $e) {
                $error = 'Erreur lors de l\'inscription. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - TechShop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
        }

        .register-box {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }

        .register-box h2 {
            text-align: center;
            color: #667eea;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .password-strength {
            font-size: 0.85rem;
            margin-top: 0.3rem;
            color: #666;
        }

        .strength-weak {
            color: #f56565;
        }

        .strength-medium {
            color: #ed8936;
        }

        .strength-strong {
            color: #48bb78;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-submit:hover {
            transform: scale(1.02);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .info-box ul {
            margin: 0.5rem 0 0 1.5rem;
        }

        .info-box li {
            margin: 0.3rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🛒 TechShop</h1>
            </div>
            <div class="nav-menu">
                <a href="index.php">Accueil</a>
                <a href="login.php">Connexion</a>
            </div>
        </div>
    </nav>

    <div class="register-container">
        <div class="register-box">
            <h2>Créer un compte</h2>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                    <br><small>Redirection vers la page de connexion...</small>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="registerForm">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur <span style="color: #f56565;">*</span></label>
                    <input type="text" id="username" name="username" required
                           placeholder="Choisissez un nom d'utilisateur"
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"
                           minlength="3">
                    <small style="color: #666;">Au moins 3 caractères</small>
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: #f56565;">*</span></label>
                    <input type="email" id="email" name="email" required
                           placeholder="votre.email@exemple.com"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe <span style="color: #f56565;">*</span></label>
                    <input type="password" id="password" name="password" required
                           placeholder="Minimum 6 caractères" minlength="6">
                    <div class="password-strength" id="strengthIndicator"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe <span style="color: #f56565;">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Retapez votre mot de passe">
                    <div id="passwordMatch" style="font-size: 0.85rem; margin-top: 0.3rem;"></div>
                </div>

                <div class="info-box">
                    <strong>🔒 Sécurité :</strong>
                    <ul>
                        <li>Votre mot de passe sera crypté automatiquement</li>
                        <li>Minimum 6 caractères requis</li>
                        <li>Utilisez un mot de passe unique</li>
                    </ul>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">S'inscrire</button>
            </form>

            <div class="login-link">
                Vous avez déjà un compte ? <a href="login.php">Se connecter</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 TechShop. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const strengthIndicator = document.getElementById('strengthIndicator');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');

        // Vérifier la force du mot de passe
        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;

            if (value.length >= 6) strength++;
            if (value.length >= 8) strength++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
            if (/\d/.test(value)) strength++;
            if (/[^a-zA-Z0-9]/.test(value)) strength++;

            if (value.length === 0) {
                strengthIndicator.textContent = '';
            } else if (strength <= 2) {
                strengthIndicator.textContent = '⚠️ Mot de passe faible';
                strengthIndicator.className = 'password-strength strength-weak';
            } else if (strength <= 3) {
                strengthIndicator.textContent = '⚡ Mot de passe moyen';
                strengthIndicator.className = 'password-strength strength-medium';
            } else {
                strengthIndicator.textContent = '✅ Mot de passe fort';
                strengthIndicator.className = 'password-strength strength-strong';
            }

            checkPasswordMatch();
        });

        // Vérifier la correspondance des mots de passe
        function checkPasswordMatch() {
            if (confirmPassword.value === '') {
                passwordMatch.textContent = '';
                submitBtn.disabled = false;
                return;
            }

            if (password.value === confirmPassword.value) {
                passwordMatch.textContent = '✅ Les mots de passe correspondent';
                passwordMatch.style.color = '#48bb78';
                submitBtn.disabled = false;
            } else {
                passwordMatch.textContent = '❌ Les mots de passe ne correspondent pas';
                passwordMatch.style.color = '#f56565';
                submitBtn.disabled = true;
            }
        }

        confirmPassword.addEventListener('input', checkPasswordMatch);

        // Validation du formulaire
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
                return false;
            }

            if (password.value.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères !');
                return false;
            }
        });
    </script>
</body>
</html>