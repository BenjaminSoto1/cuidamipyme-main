<?php
session_start();
require_once '../../config/database.php';

$user_id = $_SESSION['user']['id'];

// Obtener productos con tallas y stock
$stmt = $pdo->prepare("
    SELECT p.id as product_id, p.name, s.id as size_id, s.label, ps.stock
    FROM products p
    JOIN product_sizes ps ON p.id = ps.product_id
    JOIN sizes s ON ps.size_id = s.id
    WHERE p.user_id = ?
    ORDER BY p.name, s.label
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();

$productos = [];
foreach ($rows as $r) {
    $productos[$r['product_id']]['name'] = $r['name'];
    $productos[$r['product_id']]['tallas'][] = [
        'size_id' => $r['size_id'],
        'label' => $r['label'],
        'stock' => $r['stock']
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Venta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">➕ Registrar Venta</h2>

  <form method="POST" action="../../controllers/CreateSale.php">
    <div class="mb-3">
      <label class="form-label">Cliente</label>
      <input name="client_name" type="text" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Producto</label>
      <select name="product_id" class="form-select" required onchange="updateTallas()">
        <option value="">Seleccione un producto</option>
        <?php foreach ($productos as $pid => $p): ?>
          <option value="<?= $pid ?>"><?= $p['name'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Talla</label>
      <select name="size_id" id="talla-select" class="form-select" required>
        <option value="">Seleccione una talla</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Cantidad</label>
      <input name="quantity" type="number" min="1" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Registrar Venta</button>
    <a href="index.php" class="btn btn-secondary w-100 mt-2">← Volver</a>
  </form>
</div>

<script>
const productos = <?= json_encode($productos) ?>;

function updateTallas() {
  const productoId = document.querySelector('[name="product_id"]').value;
  const tallaSelect = document.getElementById('talla-select');
  tallaSelect.innerHTML = '<option value="">Seleccione una talla</option>';

  if (productos[productoId]) {
    productos[productoId]['tallas'].forEach(t => {
      tallaSelect.innerHTML += `<option value="${t.size_id}">${t.label} (${t.stock} disponibles)</option>`;
    });
  }
}
</script>
</body>
</html>
