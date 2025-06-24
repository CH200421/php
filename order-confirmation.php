<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Récupérer la commande
$stmt = $db->prepare("SELECT o.* FROM orders o WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails de la commande
$stmt = $db->prepare("SELECT od.*, p.name, p.image FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?");
$stmt->execute([$order['id']]);
$order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<section class="order-confirmation">
    <div class="confirmation-message">
        <i class="fas fa-check-circle"></i>
        <h2>Merci pour votre commande !</h2>
        <p>Votre commande #<?= $order['id'] ?> a été passée avec succès.</p>
        <p>Nous vous avons envoyé un email de confirmation à <?= $_SESSION['email'] ?></p>
        <a href="index.php" class="btn">Retour à la boutique</a>
    </div>
    
    <div class="order-details">
        <h3>Détails de la commande</h3>
        <div class="detail-row">
            <span>Numéro de commande</span>
            <span>#<?= $order['id'] ?></span>
        </div>
        <div class="detail-row">
            <span>Date</span>
            <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="detail-row">
            <span>Total</span>
            <span><?= number_format($order['total'], 2) ?> DH</span>
        </div>
        <div class="detail-row">
            <span>Méthode de paiement</span>
            <span><?= $order['payment_method'] ?></span>
        </div>
        
        <h3>Articles commandés</h3>
        <div class="order-items">
            <?php foreach ($order_details as $item): ?>
                <div class="order-item">
                    <img src="assets/images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
                    <div class="item-info">
                        <h4><?= $item['name'] ?></h4>
                        <p><?= $item['quantity'] ?> x <?= number_format($item['price'], 2) ?> DH</p>
                    </div>
                    <div class="item-subtotal">
                        <?= number_format($item['price'] * $item['quantity'], 2) ?> DH
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>