<?php
require_once __DIR__ . '/config.php';

function retrieveProductById(PDO $pdo, $id): array {
    $stmt = $pdo->prepare('SELECT * FROM produit WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $produit = $stmt->fetch();
    return $produit ?: [];
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produit = retrieveProductById($pdo, $id);

require_once __DIR__ . '/includes/header.php';
?>

<?php if (empty($produit)): ?>
    <p>Produit introuvable.</p>

<?php elseif ($produit['statut'] == 0): ?>
    <p>Ce produit n'est pas disponible à la vente.</p>

<?php else: ?>
    <section class="product-detail">

        <img
            src="/assets/images/<?= htmlspecialchars($produit['image']) ?>"
            alt="<?= htmlspecialchars($produit['nom']) ?>"
        >

        <div class="product-info">

            <h1><?= htmlspecialchars($produit['nom']) ?></h1>

            <p><?= htmlspecialchars($produit['description_courte']) ?></p>

            <p><?= htmlspecialchars($produit['description_longue']) ?></p>

            <p class="product-price">
                Prix HTVA : <?= number_format($produit['prixhtva'], 2, ',', '.') ?> €
            </p>
            <p class="product-price">
                Prix TVAC : <?= number_format($produit['prixhtva'] * TVA, 2, ',', '.') ?> €
            </p>

            <p class="product-stock">
                En stock : <?= $produit['stock'] ?> unités
            </p>

            <?php if ($produit['stock'] > 0): ?>
            <form id="add-to-cart-form">
                <input type="hidden" name="id" value="<?= $produit['id'] ?>">
                <div class="quantity-selector">
                    <label for="quantity">Quantité :</label>
                    <select name="quantity" id="quantity">
                        <?php for ($i = 1; $i <= min(5, $produit['stock']); $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="add-to-cart-btn">
                    🛒 Ajouter au panier
                </button>
            </form>

            <div id="cart-message"></div>
            <?php endif; ?>

        </div>
    </section>

    <script>
    document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = this.querySelector('[name="id"]').value;
        const quantity = this.querySelector('[name="quantity"]').value;

        fetch('/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            const msg = document.getElementById('cart-message');
            if (data.success) {
                msg.innerHTML = '✅ ' + data.message + ' <a href="/basket.php">Voir le panier</a>';
                msg.style.color = 'green';
            } else {
                msg.innerHTML = '❌ ' + data.message;
                msg.style.color = 'red';
            }
        });
    });
    </script>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
