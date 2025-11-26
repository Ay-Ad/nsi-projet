<?php
require_once 'config.php';

// Récupérer tous les produits
$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartCount = array_sum(getCartItems());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Votre boutique high-tech</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🛒 TechShop</h1>
            </div>
            <div class="nav-menu">
                <a href="index.php">Accueil</a>
                <a href="cart.php">Panier (<?php echo $cartCount; ?>)</a>
                <?php if (isLoggedIn()): ?>
                    <a href="orders.php">Mes commandes</a>
                    <a href="settings.php">Paramètres</a>
                    <a href="logout.php" class="btn-logout">Déconnexion</a>
                <?php else: ?>
                    <a href="register.php">Inscription</a>
                    <a href="login.php" class="btn-login">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Bienvenue chez TechShop</h2>
            <p>Découvrez nos produits high-tech de qualité</p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Nos Produits</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <div class="image-placeholder">📦</div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-footer">
                            <span class="price"><?php echo number_format($product['price'], 2); ?> €</span>
                            <span class="stock">Stock: <?php echo $product['stock']; ?></span>
                        </div>
                        <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn-add-cart">Ajouter au panier</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 TechShop. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>