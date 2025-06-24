<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Récupérer les informations utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les produits du panier
$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = getProductById($db, $product_id);
    if ($product) {
        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;
        $cart_items[] = $product;
        $total += $product['subtotal'];
    }
}

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valider les données du formulaire
    $errors = [];
    
    if (empty($_POST['payment_method'])) {
        $errors[] = "Veuillez sélectionner un mode de paiement";
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Créer la commande
            $stmt = $db->prepare("INSERT INTO orders (user_id, total, payment_method) VALUES (?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $total,
                $_POST['payment_method']
            ]);
            $order_id = $db->lastInsertId();
            
            // Ajouter les détails de la commande
            foreach ($cart_items as $item) {
                $stmt = $db->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Mettre à jour le stock
                $stmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([
                    $item['quantity'],
                    $item['id']
                ]);
            }
            
            $db->commit();
            
            // Vider le panier
            unset($_SESSION['cart']);
            
            // Rediriger vers la page de confirmation
            header('Location: order-confirmation.php?id=' . $order_id);
            exit;
            
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = "Une erreur est survenue lors du traitement de votre commande: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<section class="checkout">
    <h2>Finaliser votre commande</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <form method="post">
                <h3>Informations de livraison</h3>
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($user['city']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="postal_code">Code postal</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($user['postal_code']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="country">Pays</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>
                
                <h3>Méthode de paiement</h3>
                <div class="form-group payment-methods">
                    <label>
                        <input type="radio" name="payment_method" value="Carte de crédit" required>
                        <i class="fas fa-credit-card"></i> Carte de crédit
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="PayPal">
                        <i class="fab fa-paypal"></i> PayPal
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="Paiement à la livraison">
                        <i class="fas fa-truck"></i> Paiement à la livraison
                    </label>
                </div>
                
                <button type="submit" class="btn">Confirmer la commande</button>
            </form>
        </div>
        
        <div class="order-summary">
            <h3>Résumé de la commande</h3>
            <div class="summary-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="item-info">
                            <span class="item-name"><?= $item['name'] ?></span>
                            <span class="item-quantity">x<?= $item['quantity'] ?></span>
                        </div>
                        <span class="item-price"><?= number_format($item['subtotal'], 2) ?> DH</span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span><?= number_format($total, 2) ?> DH</span>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>