<?php
// product-add.php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$categories = getAllCategories($db);
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
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Seuls les fichiers JPEG, PNG et GIF sont autorisés";
        } else {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = uniqid() . '.' . $extension;
            $upload_path = '../assets/images/' . $image;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Erreur lors du téléchargement de l'image";
            }
        }
    } else {
        $errors[] = "Une image est requise";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("INSERT INTO products (name, category_id, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category_id, $price, $stock, $description, $image]);
            
            $_SESSION['message'] = "Produit ajouté avec succès";
            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du produit: " . $e->getMessage();
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
        <h2>Ajouter un Produit</h2>
        
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
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Catégorie</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Prix (DH)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn">Ajouter le produit</button>
        </form>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>