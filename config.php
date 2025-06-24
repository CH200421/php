<?php

// Connexion à la base de données
try {
    $db = new PDO("mysql:host=localhost;dbname=femme_accessoires", "root", "");

} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session
session_start();

// Inclure les fonctions
require_once 'functions.php';
?>