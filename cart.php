<?php
require_once 'config.php';

$cart = getCartItems();
$cartItems = [];
$total = 0;

foreach ($cart as $productId => $quantity) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;
        $total += $product['subtotal'];
        $cartItems[] = $product;
    }
}

$cartCount = array_sum($cart);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - TechShop</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🛒 TechShop</h1>
            </div>
            <div class="nav-menu">
                <a href="index.php">Accueil</a>
                <a href="cart.php" class="active">Panier (<?php echo $cartCount; ?>)</a>
                <?php if (isLoggedIn()): ?>
                    <a href="orders.php">Mes commandes</a>
                    <a href="settings.php">Paramètres</a>
                    <a href="logout.php" class="btn-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="cart-container">
        <div class="container">
            <h2>Votre Panier</h2>

            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <p>Votre panier est vide</p>
                    <a href="index.php" class="btn-continue">Continuer mes achats</a>
                </div>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">📦</div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="item-price"><?php echo number_format($item['price'], 2); ?> €</p>
                            </div>
                            <div class="item-quantity">
                                <form method="POST" action="update_cart.php" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="action" value="decrease" class="qty-btn">-</button>
                                    <span class="qty-display"><?php echo $item['quantity']; ?></span>
                                    <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                                </form>
                            </div>
                            <div class="item-subtotal">
                                <strong><?php echo number_format($item['subtotal'], 2); ?> €</strong>
                            </div>
                            <div class="item-remove">
                                <form method="POST" action="remove_from_cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn-remove">🗑️</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Total:</span>
                        <strong><?php echo number_format($total, 2); ?> €</strong>
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <a href="payment.php" class="btn-checkout">Procéder au paiement</a>
                    <?php else: ?>
                        <p class="login-required">Vous devez être connecté pour passer commande</p>
                        <a href="login.php" class="btn-checkout">Se connecter</a>
                    <?php endif; ?>

                    <a href="index.php" class="btn-continue-shopping">Continuer mes achats</a>
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