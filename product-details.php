<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product = getProductById($db, $_GET['id']);
if (!$product) {
    header('Location: index.php');
    exit;
}

require_once 'includes/header.php';
?>

<section class="product-details">
    <div class="product-images">
        <img src="assets/images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
    </div>
    <div class="product-info">
        <h1><?= $product['name'] ?></h1>
        <p class="category">Catégorie: <?= $product['category_name'] ?></p>
        <p class="price"><?= number_format($product['price'], 2) ?> DH</p>
        <p class="stock"><?= $product['stock'] > 0 ? 'En stock' : 'Rupture de stock' ?></p>
        <p class="description"><?= $product['description'] ?></p>
        
        <?php if ($product['stock'] > 0): ?>
            <form class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="quantity">
                    <label for="quantity">Quantité:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                </div>
                <button type="submit" class="btn">Ajouter au panier</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="related-products">
    <h2>Produits similaires</h2>
    <div class="product-grid">
        <?php 
        $related_products = getProductsByCategory($db, $product['category_id']);
        $displayed = 0;
        foreach ($related_products as $related_product):
            if ($related_product['id'] != $product['id'] && $displayed < 4):
                $displayed++;
        ?>
            <div class="product-card">
                <img src="assets/images/<?= $related_product['image'] ?>" alt="<?= $related_product['name'] ?>">
                <h3><?= $related_product['name'] ?></h3>
                <p class="price"><?= number_format($related_product['price'], 2) ?> DH</p>
                <div class="product-actions">
                    <a href="product-details.php?id=<?= $related_product['id'] ?>" class="btn">Voir détails</a>
                    <button class="btn add-to-cart" data-id="<?= $related_product['id'] ?>">Ajouter au panier</button>
                </div>
            </div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>