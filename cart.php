<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

if (empty($_SESSION['cart'])) {
    echo '<section class="empty-cart">
        <h2>Votre panier est vide</h2>
        <p>Parcourez notre collection et ajoutez des articles à votre panier.</p>
        <a href="index.php" class="btn">Continuer vos achats</a>
    </section>';
    require_once 'includes/footer.php';
    exit;
}

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
?>

<section class="cart">
    <h2>Votre Panier</h2>
    <div class="cart-container">
        <div class="cart-items">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td class="product-info">
                                <img src="assets/images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                                <div>
                                    <h3><?= $item['name'] ?></h3>
                                    <p><?= $item['category_name'] ?></p>
                                </div>
                            </td>
                            <td class="price"><?= number_format($item['price'], 2) ?> DH</td>
                            <td class="quantity">
                                <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" data-id="<?= $item['id'] ?>">
                            </td>
                            <td class="subtotal"><?= number_format($item['subtotal'], 2) ?> DH</td>
                            <td class="actions">
                                <button class="remove-item" data-id="<?= $item['id'] ?>"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart-summary">
            <h3>Résumé de la commande</h3>
            <div class="summary-details">
                <div class="summary-row">
                    <span>Sous-total</span>
                    <span><?= number_format($total, 2) ?> DH</span>
                </div>
                <div class="summary-row">
                    <span>Livraison</span>
                    <span>Gratuite</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?= number_format($total, 2) ?> DH</span>
                </div>
            </div>
            <a href="checkout.php" class="btn checkout-btn">Passer la commande</a>
            <a href="index.php" class="btn continue-shopping">Continuer vos achats</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>