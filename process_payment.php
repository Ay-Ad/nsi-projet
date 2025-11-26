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

            // Récupérer les informations du formulaire
            $prenom = $_POST['prenom'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $adresse = $_POST['adresse'] ?? '';
            $code_postal = $_POST['code_postal'] ?? '';
            $ville = $_POST['ville'] ?? '';
            $pays = $_POST['pays'] ?? '';
            $complement_adresse = $_POST['complement_adresse'] ?? '';

            // Informations bancaires (en production, ces données ne seraient JAMAIS stockées!)
            $carte_numero = substr($_POST['carte_numero'] ?? '', -4); // On garde seulement les 4 derniers chiffres
            $carte_nom = $_POST['carte_nom'] ?? '';

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

            // Enregistrer les informations de livraison
            $stmt = $conn->prepare("
                INSERT INTO shipping_info
                (order_id, prenom, nom, email, telephone, adresse, code_postal, ville, pays, complement_adresse, carte_derniers_chiffres)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId, $prenom, $nom, $email, $telephone,
                $adresse, $code_postal, $ville, $pays, $complement_adresse, $carte_numero
            ]);

            $conn->commit();

            // Vider le panier
            $_SESSION['cart'] = [];

            // Rediriger vers la page de confirmation
            header('Location: order_confirmation.php?order_id=' . $orderId);
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            header('Location: payment.php?error=1');
            exit();
        }
    }
}

header('Location: cart.php');
exit();
?>