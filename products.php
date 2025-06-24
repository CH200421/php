<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Récupérer tous les produits
$products = getAllProducts($db);

// Supprimer un produit
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    try {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        $_SESSION['message'] = "Produit supprimé avec succès";
        header('Location: products.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du produit: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <h3>Administration</h3>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li class="active"><a href="products.php"><i class="fas fa-tshirt"></i> Produits</a></li>
            <li><a href="categories.php"><i class="fas fa-tags"></i> Catégories</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Commandes</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>
    
    <main class="admin-content">
        <h2>Gestion des Produits</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-actions">
            <a href="product-add.php" class="btn"><i class="fas fa-plus"></i> Ajouter un produit</a>
        </div>
        
        <div class="products-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><img src="../assets/images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" width="50"></td>
                            <td><?= $product['name'] ?></td>
                            <td><?= $product['category_name'] ?? 'Non catégorisé' ?></td>
                            <td><?= number_format($product['price'], 2) ?> DH</td>
                            <td><?= $product['stock'] ?></td>
                            <td class="actions">
                                <a href="product-edit.php?id=<?= $product['id'] ?>" class="btn"><i class="fas fa-edit"></i></a>
                                <a href="products.php?delete=<?= $product['id'] ?>" class="btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>