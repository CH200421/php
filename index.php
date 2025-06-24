 <?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Récupérer les produits
if (isset($_GET['category'])) {
    $products = getProductsByCategory($db, $_GET['category']);
} else {
    $products = getAllProducts($db, 15);
}
?>

<section class="hero">
    <div class="hero-content">
        <h1>Découvrez notre collection exclusive</h1>
        <p>Des accessoires élégants pour toutes les occasions</p>
        <a href="#products" class="btn">Voir la collection</a>
    </div>
</section>

<section id="products" class="products">
    <h2>Nos Produits</h2>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="assets/images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                <h3><?= $product['name'] ?></h3>
                <p class="price"><?= number_format($product['price'], 2) ?> DH</p>
                <div class="product-actions">
                    <a href="product-details.php?id=<?= $product['id'] ?>" class="btn">Voir détails</a>
                    <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">Ajouter au panier</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>       