<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartCount = array_sum(getCartItems());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - TechShop</title>
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
                <a href="cart.php">Panier (<?php echo $cartCount; ?>)</a>
                <a href="orders.php" class="active">Mes commandes</a>
                <a href="settings.php">Paramètres</a>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="orders-container">
        <div class="container">
            <h2>Mes Commandes</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">✅ Commande validée avec succès!</div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <p>Vous n'avez pas encore passé de commande</p>
                    <a href="index.php" class="btn-continue">Découvrir nos produits</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order):
                        $stmt = $conn->prepare("
                            SELECT oi.*, p.name
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?
                        ");
                        $stmt->execute([$order['id']]);
                        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <strong>Commande #<?php echo $order['id']; ?></strong>
                                <span class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <strong class="order-total"><?php echo number_format($order['total'], 2); ?> €</strong>
                            </div>
                        </div>
                        <div class="order-items">
                            <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                <span>x<?php echo $item['quantity']; ?></span>
                                <span><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 TechShop. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>