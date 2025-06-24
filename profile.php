<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Récupérer les informations utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Mettre à jour les informations
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $email = cleanInput($_POST['email']);
    $address = cleanInput($_POST['address']);
    $city = cleanInput($_POST['city']);
    $postal_code = cleanInput($_POST['postal_code']);
    $country = cleanInput($_POST['country']);
    $phone = cleanInput($_POST['phone']);
    
    // Validation
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, city = ?, postal_code = ?, country = ?, phone = ? WHERE id = ?");
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $address,
            $city,
            $postal_code,
            $country,
            $phone,
            $_SESSION['user_id']
        ]);
        
        $_SESSION['email'] = $email;
        $success = true;
        
        // Recharger les informations utilisateur
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Récupérer les commandes de l'utilisateur
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/head.php';
?>

<section class="profile">
    <h2>Mon Profil</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Vos informations ont été mises à jour avec succès.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="profile-container">
        <div class="profile-form">
            <form method="post">
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <textarea id="address" name="address"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($user['city']) ?>">
                </div>
                <div class="form-group">
                    <label for="postal_code">Code postal</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($user['postal_code']) ?>">
                </div>
                <div class="form-group">
                    <label for="country">Pays</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <button type="submit" class="btn">Mettre à jour</button>
            </form>
        </div>
        
        <div class="profile-orders">
            <h3>Mes Commandes</h3>
            
            <?php if (empty($orders)): ?>
                <p>Vous n'avez pas encore passé de commande.</p>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-id">Commande #<?= $order['id'] ?></span>
                                <span class="order-date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                                <span class="order-status <?= str_replace(' ', '-', $order['status']) ?>"><?= $order['status'] ?></span>
                            </div>
                            <div class="order-details">
                                <span class="order-total"><?= number_format($order['total'], 2) ?> DH</span>
                                <a href="order-details.php?id=<?= $order['id'] ?>" class="btn">Voir détails</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>