<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user']['id'];

// Obtener el producto
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Producto no encontrado.";
    exit;
}

// Obtener marcas, categorías, tallas y stock actual
$marcas = $pdo->query("SELECT id, name FROM brands")->fetchAll();
$categorias = $pdo->query("SELECT id, name FROM categories")->fetchAll();
$tallas = $pdo->query("SELECT id, label FROM sizes")->fetchAll();

$stmt = $pdo->prepare("SELECT size_id, stock FROM product_sizes WHERE product_id = ?");
$stmt->execute([$product['id']]);
$stock_por_talla = [];
foreach ($stmt->fetchAll() as $row) {
    $stock_por_talla[$row['size_id']] = $row['stock'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="card-title text-center mb-4">✏️ Editar Producto</h4>
          <form method="POST" action="../../controllers/UpdateProduct.php">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">

            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input name="name" type="text" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Precio</label>
              <input name="price" type="number" step="0.01" class="form-control" value="<?= $product['price'] ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Marca</label>
              <select name="brand_id" class="form-select" required>
                <?php foreach ($marcas as $m): ?>
                  <option value="<?= $m['id'] ?>" <?= $m['id'] == $product['brand_id'] ? 'selected' : '' ?>>
                    <?= $m['name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Categoría</label>
              <select name="category_id" class="form-select" required>
                <?php foreach ($categorias as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                    <?= $c['name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Tallas y stock</label>
              <?php foreach ($tallas as $t): 
                $checked = isset($stock_por_talla[$t['id']]);
                $cantidad = $checked ? $stock_por_talla[$t['id']] : '';
              ?>
              <div class="input-group mb-1">
                <div class="input-group-text">
                  <input class="form-check-input mt-0" type="checkbox" name="sizes[<?= $t['id'] ?>]" <?= $checked ? 'checked' : '' ?>>
                </div>
                <span class="input-group-text"><?= $t['label'] ?></span>
                <input type="number" name="stock[<?= $t['id'] ?>]" class="form-control" placeholder="Stock para <?= $t['label'] ?>" value="<?= $cantidad ?>">
              </div>
              <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-warning w-100">Actualizar</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">← Volver</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
