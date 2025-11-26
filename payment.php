<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$cart = getCartItems();
if (empty($cart)) {
    header('Location: cart.php');
    exit();
}

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
    <title>Paiement - TechShop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            padding: 2rem 0 4rem;
        }

        .payment-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .payment-form h2 {
            color: #667eea;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        .section-title {
            color: #333;
            font-size: 1.3rem;
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .required {
            color: #f56565;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .card-icons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .card-icon {
            padding: 0.3rem 0.6rem;
            background: #f7fafc;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .order-summary h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-product {
            font-size: 0.95rem;
            color: #666;
        }

        .summary-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #e2e8f0;
        }

        .btn-pay {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: transform 0.2s;
        }

        .btn-pay:hover {
            transform: scale(1.02);
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #48bb78;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        @media (max-width: 968px) {
            .payment-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
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
        <div class="payment-container">
            <!-- Formulaire de paiement -->
            <div class="payment-form">
                <h2>Informations de paiement</h2>

                <form method="POST" action="process_payment.php" id="paymentForm">
                    <!-- Informations personnelles -->
                    <h3 class="section-title">📋 Informations personnelles</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Prénom <span class="required">*</span></label>
                            <input type="text" name="prenom" required placeholder="Jean">
                        </div>
                        <div class="form-group">
                            <label>Nom <span class="required">*</span></label>
                            <input type="text" name="nom" required placeholder="Dupont">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required placeholder="jean.dupont@email.com">
                    </div>

                    <div class="form-group">
                        <label>Téléphone <span class="required">*</span></label>
                        <input type="tel" name="telephone" required placeholder="06 12 34 56 78">
                    </div>

                    <!-- Adresse de livraison -->
                    <h3 class="section-title">📍 Adresse de livraison</h3>

                    <div class="form-group">
                        <label>Adresse <span class="required">*</span></label>
                        <input type="text" name="adresse" required placeholder="12 Rue de la Paix">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Code postal <span class="required">*</span></label>
                            <input type="text" name="code_postal" required placeholder="75000">
                        </div>
                        <div class="form-group">
                            <label>Ville <span class="required">*</span></label>
                            <input type="text" name="ville" required placeholder="Paris">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pays <span class="required">*</span></label>
                        <select name="pays" required>
                            <option value="">Sélectionnez un pays</option>
                            <option value="France" selected>France</option>
                            <option value="Belgique">Belgique</option>
                            <option value="Suisse">Suisse</option>
                            <option value="Canada">Canada</option>
                            <option value="Maroc">Maroc</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Informations complémentaires</label>
                        <textarea name="complement_adresse" placeholder="Bâtiment, étage, code d'accès..."></textarea>
                    </div>

                    <!-- Informations bancaires -->
                    <h3 class="section-title">💳 Informations bancaires</h3>

                    <div class="form-group">
                        <label>Numéro de carte <span class="required">*</span></label>
                        <input type="text" name="carte_numero" required placeholder="1234 5678 9012 3456" maxlength="19" id="cardNumber">
                        <div class="card-icons">
                            <span class="card-icon">VISA</span>
                            <span class="card-icon">MC</span>
                            <span class="card-icon">AMEX</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nom sur la carte <span class="required">*</span></label>
                        <input type="text" name="carte_nom" required placeholder="JEAN DUPONT">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date d'expiration <span class="required">*</span></label>
                            <input type="text" name="carte_expiration" required placeholder="MM/AA" maxlength="5" id="cardExpiry">
                        </div>
                        <div class="form-group">
                            <label>CVV <span class="required">*</span></label>
                            <input type="text" name="carte_cvv" required placeholder="123" maxlength="3" id="cardCVV">
                        </div>
                    </div>

                    <button type="submit" class="btn-pay">🔒 Payer <?php echo number_format($total, 2); ?> €</button>
                    <div class="secure-badge">
                        🔒 Paiement 100% sécurisé
                    </div>
                </form>
            </div>

            <!-- Récapitulatif de commande -->
            <div class="order-summary">
                <h3>Récapitulatif</h3>

                <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <div>
                        <div><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="summary-product">Quantité: <?php echo $item['quantity']; ?></div>
                    </div>
                    <div><strong><?php echo number_format($item['subtotal'], 2); ?> €</strong></div>
                </div>
                <?php endforeach; ?>

                <div class="summary-item">
                    <div>Sous-total</div>
                    <div><?php echo number_format($total, 2); ?> €</div>
                </div>

                <div class="summary-item">
                    <div>Livraison</div>
                    <div>Gratuite</div>
                </div>

                <div class="summary-total">
                    <div class="summary-item">
                        <div>Total</div>
                        <div><?php echo number_format($total, 2); ?> €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 TechShop. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Formatage automatique du numéro de carte
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        // Formatage de la date d'expiration
        document.getElementById('cardExpiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });

        // Validation CVV (chiffres uniquement)
        document.getElementById('cardCVV').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Validation du formulaire
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                e.preventDefault();
                alert('Numéro de carte invalide');
                return false;
            }
        });
    </script>
</body>
</html>