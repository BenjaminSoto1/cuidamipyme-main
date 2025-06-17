<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit;
}

$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user']['id'];

if (!$product_id) {
    $_SESSION['error'] = "Producto no especificado.";
    header("Location: index.php");
    exit;
}

// Obtener producto
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = "Producto no encontrado.";
    header("Location: index.php");
    exit;
}

// Obtener tallas asociadas
$stmt = $pdo->prepare("
    SELECT ps.id as ps_id, s.label, ps.stock 
    FROM product_sizes ps
    JOIN sizes s ON s.id = ps.size_id
    WHERE ps.product_id = ?
");
$stmt->execute([$product_id]);
$tallas = $stmt->fetchAll();
?>

<h2>🧵 Editar Stock: <?= htmlspecialchars($product['name']) ?></h2>

<form action="../../controllers/UpdateStock.php" method="POST">
  <input type="hidden" name="product_id" value="<?= $product_id ?>">

  <table border="1" cellpadding="5">
    <tr><th>Talla</th><th>Stock</th></tr>
    <?php foreach ($tallas as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['label']) ?></td>
        <td>
          <input type="number" name="stock[<?= $t['ps_id'] ?>]" value="<?= $t['stock'] ?>" min="0">
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <br>
  <button type="submit">💾 Guardar cambios</button>
  <a href="index.php">🔙 Volver</a>
</form>
