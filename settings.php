<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profile') {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');

        if (empty($new_username) || empty($new_email)) {
            $error = 'Tous les champs sont obligatoires';
        } elseif (strlen($new_username) < 3) {
            $error = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide';
        } else {
            // Vérifier si le nom d'utilisateur ou l'email existe déjà (sauf pour l'utilisateur actuel)
            $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$new_username, $new_email, $_SESSION['user_id']]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $error = 'Ce nom d\'utilisateur ou cet email est déjà utilisé';
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$new_username, $new_email, $_SESSION['user_id']]);

                    $_SESSION['username'] = $new_username;
                    $user['username'] = $new_username;
                    $user['email'] = $new_email;

                    $success = 'Profil mis à jour avec succès !';
                } catch (PDOException $e) {
                    $error = 'Erreur lors de la mise à jour';
                }
            }
        }
    }

    // Changement de mot de passe
    elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Tous les champs sont obligatoires';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Mot de passe actuel incorrect';
        } elseif (strlen($new_password) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Les nouveaux mots de passe ne correspondent pas';
        } else {
            try {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);

                $success = 'Mot de passe modifié avec succès !';
            } catch (PDOException $e) {
                $error = 'Erreur lors du changement de mot de passe';
            }
        }
    }

    // Suppression du compte
    elseif ($_POST['action'] === 'delete_account') {
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (!password_verify($password_confirm, $user['password'])) {
            $error = 'Mot de passe incorrect';
        } else {
            try {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);

                session_destroy();
                header('Location: index.php?account_deleted=1');
                exit();
            } catch (PDOException $e) {
                $error = 'Erreur lors de la suppression du compte';
            }
        }
    }
}

$cartCount = array_sum(getCartItems());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - TechShop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 2rem auto 4rem;
            padding: 0 20px;
        }

        .settings-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
            text-align: center;
        }

        .settings-header h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .settings-header p {
            color: #666;
        }

        .settings-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
        }

        .settings-section h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .btn-primary {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            transform: scale(1.02);
        }

        .btn-danger {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-danger:hover {
            transform: scale(1.02);
        }

        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .danger-zone {
            background: #fff5f5;
            border: 2px solid #feb2b2;
            padding: 1.5rem;
            border-radius: 10px;
        }

        .danger-zone h3 {
            color: #c53030;
            margin-bottom: 1rem;
        }

        .danger-zone p {
            color: #666;
            margin-bottom: 1rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
        }

        .quick-action-card .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .quick-action-card h3 {
            color: #333;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .quick-action-card p {
            color: #666;
            font-size: 0.9rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            color: #c53030;
            margin: 0;
        }

        .close {
            font-size: 2rem;
            font-weight: bold;
            color: #999;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
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
                <a href="cart.php">Panier (<?php echo $cartCount; ?>)</a>
                <a href="orders.php">Mes commandes</a>
                <a href="settings.php" class="active">Paramètres</a>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="settings-container">
        <!-- En-tête -->
        <div class="settings-header">
            <h1>⚙️ Paramètres du compte</h1>
            <p>Gérez vos informations personnelles et vos préférences</p>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Actions rapides -->
        <div class="quick-actions">
            <div class="quick-action-card">
                <div class="icon">👤</div>
                <h3>Profil</h3>
                <p>Nom d'utilisateur et email</p>
            </div>
            <div class="quick-action-card">
                <div class="icon">🔒</div>
                <h3>Sécurité</h3>
                <p>Mot de passe</p>
            </div>
            <div class="quick-action-card">
                <div class="icon">📦</div>
                <h3>Commandes</h3>
                <p><?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $orderCount = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo $orderCount['count'];
                ?> commandes</p>
            </div>
            <div class="quick-action-card">
                <div class="icon">📅</div>
                <h3>Membre depuis</h3>
                <p><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <!-- Section Profil -->
        <div class="settings-section">
            <h2>👤 Informations du profil</h2>
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <button type="submit" class="btn-primary">💾 Enregistrer les modifications</button>
            </form>
        </div>

        <!-- Section Mot de passe -->
        <div class="settings-section">
            <h2>🔒 Changer le mot de passe</h2>
            <form method="POST" action="settings.php" id="passwordForm">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password" required placeholder="Entrez votre mot de passe actuel">
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" id="new_password" required minlength="6" placeholder="Minimum 6 caractères">
                    <div id="passwordStrength" style="font-size: 0.85rem; margin-top: 0.3rem;"></div>
                </div>

                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Retapez le nouveau mot de passe">
                    <div id="passwordMatch" style="font-size: 0.85rem; margin-top: 0.3rem;"></div>
                </div>

                <button type="submit" class="btn-primary" id="changePasswordBtn">🔐 Changer le mot de passe</button>
            </form>
        </div>

        <!-- Zone de danger -->
        <div class="settings-section">
            <div class="danger-zone">
                <h3>⚠️ Zone de danger</h3>
                <p><strong>Attention :</strong> La suppression de votre compte est définitive et irréversible. Toutes vos commandes et informations seront supprimées.</p>
                <button type="button" class="btn-danger" onclick="openDeleteModal()">🗑️ Supprimer mon compte</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚠️ Confirmer la suppression</h2>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <p style="color: #666; margin-bottom: 1.5rem;">
                Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est <strong>irréversible</strong>.
            </p>
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="delete_account">
                <div class="form-group">
                    <label>Confirmez avec votre mot de passe</label>
                    <input type="password" name="password_confirm" required placeholder="Entrez votre mot de passe">
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="button" class="btn-primary" onclick="closeDeleteModal()" style="flex: 1;">Annuler</button>
                    <button type="submit" class="btn-danger" style="flex: 1;">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 TechShop. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Vérification de la force du mot de passe
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');
        const changePasswordBtn = document.getElementById('changePasswordBtn');

        newPassword.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;

            if (value.length >= 6) strength++;
            if (value.length >= 8) strength++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
            if (/\d/.test(value)) strength++;
            if (/[^a-zA-Z0-9]/.test(value)) strength++;

            if (value.length === 0) {
                passwordStrength.textContent = '';
            } else if (strength <= 2) {
                passwordStrength.textContent = '⚠️ Faible';
                passwordStrength.style.color = '#f56565';
            } else if (strength <= 3) {
                passwordStrength.textContent = '⚡ Moyen';
                passwordStrength.style.color = '#ed8936';
            } else {
                passwordStrength.textContent = '✅ Fort';
                passwordStrength.style.color = '#48bb78';
            }

            checkPasswordMatch();
        });

        function checkPasswordMatch() {
            if (confirmPassword.value === '') {
                passwordMatch.textContent = '';
                changePasswordBtn.disabled = false;
                return;
            }

            if (newPassword.value === confirmPassword.value) {
                passwordMatch.textContent = '✅ Les mots de passe correspondent';
                passwordMatch.style.color = '#48bb78';
                changePasswordBtn.disabled = false;
            } else {
                passwordMatch.textContent = '❌ Les mots de passe ne correspondent pas';
                passwordMatch.style.color = '#f56565';
                changePasswordBtn.disabled = true;
            }
        }

        confirmPassword.addEventListener('input', checkPasswordMatch);

        // Modal de suppression
        function openDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>