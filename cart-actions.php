<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Action non spécifiée']);
    exit;
}

$action = $_POST['action'];

switch ($action) {
    case 'add':
        if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            exit;
        }
        
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Vérifier si le produit existe
        $product = getProductById($db, $product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }
        
        // Vérifier le stock
        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
            exit;
        }
        
        // Ajouter au panier
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        echo json_encode([
            'success' => true,
            'cart_count' => count($_SESSION['cart']),
            'message' => 'Produit ajouté au panier'
        ]);
        break;
        
    case 'update':
        if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            exit;
        }
        
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Vérifier si le produit existe
        $product = getProductById($db, $product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }
        
        // Vérifier le stock
        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
            exit;
        }
        
        // Mettre à jour la quantité
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        echo json_encode([
            'success' => true,
            'cart_count' => count($_SESSION['cart'])
        ]);
        break;
        
    case 'remove':
        if (!isset($_POST['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            exit;
        }
        
        $product_id = (int)$_POST['product_id'];
        
        // Supprimer du panier
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        
        echo json_encode([
            'success' => true,
            'cart_count' => isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
?>