<?php
require_once __DIR__ . '/config.php';

function retrieveCategories(PDO $pdo): array {
    $stmt = $pdo->query('
        SELECT DISTINCT c.id, c.nom
        FROM categorie c
        INNER JOIN produit_categorie pc ON c.id = pc.id_categorie
        INNER JOIN produit p ON pc.id_produit = p.id
        WHERE p.statut = 1
        ORDER BY c.nom ASC
    ');
    return $stmt->fetchAll();
}

function retrieveBuyableProducts(PDO $pdo, array $categories = [], string $order = 'priorite'): array {
    $sql = '
        SELECT DISTINCT p.*
        FROM produit p
    ';

    if (!empty($categories)) {
        $sql .= '
            INNER JOIN produit_categorie pc ON p.id = pc.id_produit
        ';
    }

    $sql .= ' WHERE p.statut = 1 ';

    $params = [];

    if (!empty($categories)) {
        $placeholders = [];
        foreach ($categories as $i => $catId) {
            $catId = (int)$catId;
            if ($catId > 0) {
                $placeholders[] = ':cat' . $i;
                $params[':cat' . $i] = $catId;
            }
        }
        if (!empty($placeholders)) {
            $sql .= ' AND pc.id_categorie IN (' . implode(',', $placeholders) . ')';
        }
    }

    if ($order === 'prix_asc') {
        $sql .= ' ORDER BY p.prixhtva ASC';
    } elseif ($order === 'prix_desc') {
        $sql .= ' ORDER BY p.prixhtva DESC';
    } else {
        $sql .= ' ORDER BY p.prioritevente ASC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

$categories = retrieveCategories($pdo);

$selectedCats = [];
if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    foreach ($_GET['categories'] as $cat) {
        $cat = (int)$cat;
        if ($cat > 0) {
            $selectedCats[] = $cat;
        }
    }
}

$order = 'priorite';
if (isset($_GET['order']) && $_GET['order'] === 'prix_asc') {
    $order = 'prix_asc';
} elseif (isset($_GET['order']) && $_GET['order'] === 'prix_desc') {
    $order = 'prix_desc';
}

$produits = retrieveBuyableProducts($pdo, $selectedCats, $order);

require_once __DIR__ . '/includes/header.php';
?>

<h1>Nos Vêtements</h1>

<!-- FORMULAIRE DE FILTRE -->
<form method="GET" action="/product.php" class="filter-form">

    <div class="filter-categories">
        <strong>Filtrer par catégorie :</strong>
        <?php foreach ($categories as $cat): ?>
            <label>
                <input
                    type="checkbox"
                    name="categories[]"
                    value="<?= $cat['id'] ?>"
                    <?= in_array($cat['id'], $selectedCats) ? 'checked' : '' ?>
                >
                <?= htmlspecialchars($cat['nom']) ?>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="filter-order">
        <strong>Trier par :</strong>
        <label>
            <input type="radio" name="order" value="priorite"
                <?= $order === 'priorite' ? 'checked' : '' ?>>
            Par défaut
        </label>
        <label>
            <input type="radio" name="order" value="prix_asc"
                <?= $order === 'prix_asc' ? 'checked' : '' ?>>
            Prix croissant
        </label>
        <label>
            <input type="radio" name="order" value="prix_desc"
                <?= $order === 'prix_desc' ? 'checked' : '' ?>>
            Prix décroissant
        </label>
    </div>

    <button type="submit" class="filter-btn">Appliquer</button>

</form>

<!-- LISTE DES PRODUITS -->
<div class="products-grid">
    <?php foreach ($produits as $produit): ?>
    <article class="product-card">
        <img
            src="/assets/images/<?= htmlspecialchars($produit['image']) ?>"
            alt="<?= htmlspecialchars($produit['nom']) ?>"
        >
        <div class="product-card-body">
            <h2><?= htmlspecialchars($produit['nom']) ?></h2>
            <p><?= htmlspecialchars($produit['description_courte']) ?></p>
            <p class="price">
                <?= number_format($produit['prixhtva'] * TVA, 2, ',', '.') ?> € TVAC
            </p>
            <a href="/products.php?id=<?= $produit['id'] ?>">Voir le produit</a>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
