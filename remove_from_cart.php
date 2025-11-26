<?php
// ========================================
// FICHIER 3: remove_from_cart.php
// ========================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? 0;

    if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

header('Location: cart.php');
exit();
?>

<?php