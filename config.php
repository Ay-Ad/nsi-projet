<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shop_db');

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Fonction pour obtenir les produits du panier
function getCartItems()
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

// Fonction pour calculer le total du panier
function getCartTotal($conn)
{
    $cart = getCartItems();
    $total = 0;

    foreach ($cart as $productId => $quantity) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }

    return $total;
}
?>