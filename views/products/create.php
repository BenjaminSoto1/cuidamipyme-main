<?php
session_start();
require_once '../../config/database.php';

// Obtener marcas y categorías
$marcas = $pdo->query("SELECT id, name FROM brands")->fetchAll();
$categorias = $pdo->query("SELECT id, name FROM categories")->fetchAll();
$tallas = $pdo->query("SELECT id, label FROM sizes")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="card-title text-center mb-4">➕ Agregar Producto</h4>
          <form method="POST" action="../../controllers/CreateProduct.php">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input name="name" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Precio</label>
              <input name="price" type="number" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
  <label class="form-label">Marca</label>
  <select name="brand_id" class="form-select">
    <option value="">Selecciona una marca existente</option>
    <?php foreach ($marcas as $m): ?>
      <option value="<?= $m['id'] ?>"><?= $m['name'] ?></option>
    <?php endforeach; ?>
  </select>
  <input name="nueva_marca" class="form-control mt-2" placeholder="(O escribe una nueva marca)">
</div>

            <div class="mb-3">
  <label class="form-label">Categoría</label>
  <select name="category_id" class="form-select">
    <option value="">Selecciona una categoría existente</option>
    <?php foreach ($categorias as $c): ?>
      <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
    <?php endforeach; ?>
  </select>
  <input name="nueva_categoria" class="form-control mt-2" placeholder="(O escribe una nueva categoría)">
</div>


<div class="mb-3">
  <label class="form-label">Tallas y Stock</label>
  <div id="talla-container">
    <div class="row g-2 mb-2">
      <div class="col-6">
        <input type="text" name="tallas[]" class="form-control" placeholder="Talla (ej: S, 38)">
      </div>
      <div class="col-6">
        <input type="number" name="stocks[]" class="form-control" placeholder="Stock de esa talla" min="0">
      </div>
    </div>
  </div>
  <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarTalla()">➕ Agregar otra talla</button>
</div>

<script>
function agregarTalla() {
  const container = document.getElementById('talla-container');
  const div = document.createElement('div');
  div.className = "row g-2 mb-2";
  div.innerHTML = `
    <div class="col-6">
      <input type="text" name="tallas[]" class="form-control" placeholder="Talla (ej: L, 42)">
    </div>
    <div class="col-6">
      <input type="number" name="stocks[]" class="form-control" placeholder="Stock de esa talla" min="0">
    </div>
  `;
  container.appendChild(div);
}
</script>



</div>


            <button type="submit" class="btn btn-success w-100">Guardar</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">← Volver</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
