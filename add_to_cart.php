<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? (int)$data['id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produit invalide.']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM produit WHERE id = :id AND statut = 1');
$stmt->execute([':id' => $id]);
$produit = $stmt->fetch();

if (!$produit) {
    echo json_encode(['success' => false, 'message' => 'Produit introuvable ou indisponible.']);
    exit;
}

if ($quantity > $produit['stock']) {
    echo json_encode(['success' => false, 'message' => 'Stock insuffisant.']);
    exit;
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if (isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id] += $quantity;
} else {
    $_SESSION['panier'][$id] = $quantity;
}

echo json_encode([
    'success' => true,
    'message' => 'Produit ajouté au panier !',
    'panier' => $_SESSION['panier']
]);