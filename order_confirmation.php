<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$orderId = $_GET['order_id'] ?? 0;

// Récupérer les détails de la commande
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Récupérer les articles de la commande
$stmt = $conn->prepare("
    SELECT oi.*, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les informations de livraison
$stmt = $conn->prepare("SELECT * FROM shipping_info WHERE order_id = ?");
$stmt->execute([$orderId]);
$shipping = $stmt->fetch(PDO::FETCH_ASSOC);

$cartCount = array_sum(getCartItems());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - TechShop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto 4rem;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .success-icon {
            text-align: center;
            font-size: 80px;
            margin-bottom: 1rem;
        }

        .confirmation-container h1 {
            text-align: center;
            color: #48bb78;
            margin-bottom: 0.5rem;
        }

        .order-number {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .info-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f7fafc;
            border-radius: 10px;
        }

        .info-section h3 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 0.8rem;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .items-list {
            margin: 1.5rem 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 2px solid #e2e8f0;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: scale(1.02);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #f7fafc;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .delivery-estimate {
            text-align: center;
            padding: 1rem;
            background: #c6f6d5;
            color: #22543d;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-weight: 600;
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
                <a href="settings.php">Paramètres</a>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="confirmation-container">
            <div class="success-icon">✅</div>
            <h1>Commande confirmée !</h1>
            <p class="order-number">Numéro de commande : #<?php echo $order['id']; ?></p>

            <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                Merci pour votre commande ! Un email de confirmation a été envoyé à <strong><?php echo htmlspecialchars($shipping['email'] ?? ''); ?></strong>
            </p>

            <!-- Informations de livraison -->
            <div class="info-section">
                <h3>📍 Adresse de livraison</h3>
                <div class="info-grid">
                    <div class="info-label">Destinataire:</div>
                    <div class="info-value"><?php echo htmlspecialchars($shipping['prenom'] . ' ' . $shipping['nom']); ?></div>

                    <div class="info-label">Adresse:</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($shipping['adresse']); ?><br>
                        <?php if ($shipping['complement_adresse']): ?>
                            <?php echo htmlspecialchars($shipping['complement_adresse']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($shipping['code_postal'] . ' ' . $shipping['ville']); ?><br>
                        <?php echo htmlspecialchars($shipping['pays']); ?>
                    </div>

                    <div class="info-label">Téléphone:</div>
                    <div class="info-value"><?php echo htmlspecialchars($shipping['telephone']); ?></div>
                </div>
            </div>

            <!-- Détails de la commande -->
            <div class="info-section">
                <h3>📦 Détails de la commande</h3>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                            <span style="color: #666;">Quantité: <?php echo $item['quantity']; ?></span>
                        </div>
                        <div><strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</strong></div>
                    </div>
                    <?php endforeach; ?>

                    <div class="total-row">
                        <span>Total payé</span>
                        <span><?php echo number_format($order['total'], 2); ?> €</span>
                    </div>
                </div>
            </div>

            <!-- Informations de paiement -->
            <div class="info-section">
                <h3>💳 Paiement</h3>
                <div class="info-grid">
                    <div class="info-label">Mode:</div>
                    <div class="info-value">Carte bancaire (•••• <?php echo htmlspecialchars($shipping['carte_derniers_chiffres'] ?? ''); ?>)</div>

                    <div class="info-label">Statut:</div>
                    <div class="info-value" style="color: #48bb78; font-weight: bold;">✓ Paiement accepté</div>
                </div>
            </div>

            <div class="delivery-estimate">
                📅 Livraison estimée: 3-5 jours ouvrés
            </div>

            <div class="action-buttons">
                <a href="orders.php" class="btn btn-secondary">Voir mes commandes</a>
                <a href="index.php" class="btn btn-primary">Continuer mes achats</a>
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