<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$productId]++;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$productId]--;
            if ($_SESSION['cart'][$productId] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }
    }
}

header('Location: cart.php');
exit();
?>