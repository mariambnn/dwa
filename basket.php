<?php
session_start();
require_once __DIR__ . '/config.php';

function retrieveProductsByIds(PDO $pdo, array $ids): array {
    if (empty($ids)) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    return $stmt->fetchAll();
}

function calculateTotals(array $produits, array $panier): array {
    $totalHtva = 0;
    $totalArticles = 0;
    foreach ($produits as $produit) {
        $quantite = $panier[$produit['id']];
        $totalHtva += $produit['prixhtva'] * $quantite;
        $totalArticles += $quantite;
    }
    return [
        'totalHtva'     => $totalHtva,
        'totalTvac'     => $totalHtva * TVA,
        'totalArticles' => $totalArticles,
    ];
}

$panier = $_SESSION['panier'] ?? [];
$ids = array_keys($panier);
$produits = retrieveProductsByIds($pdo, $ids);
$totaux = calculateTotals($produits, $panier);

require_once __DIR__ . '/includes/header.php';
?>

<style>
.basket-table td img { width: 60px !important; height: 60px !important; object-fit: cover; border-radius: 4px; margin-right: 1rem; vertical-align: middle; }
</style>

<h1>Mon Panier</h1>

<?php if (empty($panier)): ?>
    <p>Votre panier est vide. <a href="/product.php">Voir les produits</a></p>

<?php else: ?>

<div class="basket">
    <table class="basket-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): ?>
            <?php $quantite = $panier[$produit['id']]; ?>
            <tr>
                <td>
                    <img
                        src="/assets/images/<?= htmlspecialchars($produit['image']) ?>"
                        alt="<?= htmlspecialchars($produit['nom']) ?>"
                    >
                    <span><?= htmlspecialchars($produit['nom']) ?></span>
                </td>
                <td><?= number_format($produit['prixhtva'] * TVA, 2, ',', '.') ?> € TVAC</td>
                <td><?= $quantite ?></td>
                <td><?= number_format($produit['prixhtva'] * TVA * $quantite, 2, ',', '.') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="basket-totals">
        <p><strong>Nombre d'articles :</strong> <?= $totaux['totalArticles'] ?></p>
        <p><strong>Total HTVA :</strong> <?= number_format($totaux['totalHtva'], 2, ',', '.') ?> €</p>
        <p><strong>Total TVAC :</strong> <?= number_format($totaux['totalTvac'], 2, ',', '.') ?> €</p>
    </div>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>