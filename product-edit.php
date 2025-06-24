<?php
// product-edit.php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = (int)$_GET['id'];
$categories = getAllCategories($db);

// Récupérer le produit
$product = getProductById($db, $product_id);
if (!$product) {
    header('Location: products.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name']);
    $category_id = cleanInput($_POST['category_id']);
    $price = cleanInput($_POST['price']);
    $stock = cleanInput($_POST['stock']);
    $description = cleanInput($_POST['description']);
    
    // Validation
    if (empty($name)) {
        $errors[] = "Le nom du produit est requis";
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Le prix doit être un nombre positif";
    }
    
    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errors[] = "Le stock doit être un nombre positif ou zéro";
    }
    
    // Gestion de l'image
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Seuls les fichiers JPEG, PNG et GIF sont autorisés";
        } else {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_image = uniqid() . '.' . $extension;
            $upload_path = '../assets/images/' . $new_image;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Supprimer l'ancienne image
                if (file_exists('../assets/images/' . $image)) {
                    unlink('../assets/images/' . $image);
                }
                $image = $new_image;
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image";
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, description = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $category_id, $price, $stock, $description, $image, $product_id]);
            
            $_SESSION['message'] = "Produit mis à jour avec succès";
            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
        }
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
        <h2>Modifier le Produit</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nom du produit</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Catégorie</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= $category['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Prix (DH)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" min="0" value="<?= $product['stock'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($product['image']): ?>
                    <div class="current-image">
                        <p>Image actuelle:</p>
                        <img src="../assets/images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" width="100">
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>