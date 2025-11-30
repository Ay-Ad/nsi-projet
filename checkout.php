<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = getCartItems();

    if (!empty($cart)) {
        try {
            $conn->beginTransaction();

            // Créer la commande
            $total = getCartTotal($conn);
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'confirmed')");
            $stmt->execute([$_SESSION['user_id'], $total]);
            $orderId = $conn->lastInsertId();

            // Ajouter les articles de la commande
            foreach ($cart as $productId => $quantity) {
                $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$orderId, $productId, $quantity, $product['price']]);

                    // Mettre à jour le stock
                    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$quantity, $productId]);
                }
            }

            $conn->commit();

            // Vider le panier
            $_SESSION['cart'] = [];

            header('Location: orders.php?success=1');
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            header('Location: cart.php?error=1');
            exit();
        }
    }
}

header('Location: cart.php');
exit();
?>