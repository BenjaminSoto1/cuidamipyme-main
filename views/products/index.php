<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Obtener todos los productos del usuario
$stmt = $pdo->prepare("SELECT p.*, b.name as brand_name, c.name as category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.user_id = ?");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll();

// Obtener tallas por producto
$product_ids = array_column($products, 'id');
$tallas_map = [];

if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $talla_stmt = $pdo->prepare("
        SELECT ps.product_id, s.label, ps.stock
        FROM product_sizes ps
        JOIN sizes s ON ps.size_id = s.id
        WHERE ps.product_id IN ($placeholders)
    ");
    $talla_stmt->execute($product_ids);

    foreach ($talla_stmt->fetchAll() as $row) {
        $tallas_map[$row['product_id']][] = "{$row['label']} ({$row['stock']})";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">🛒 Mis Productos</h2>

    <a href="create.php" class="btn btn-success btn-sm mb-3">➕ Agregar Producto</a>

    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Tallas (Stock)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['brand_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name']) ?></td>
                <td>$<?= number_format($product['price'], 0, ',', '.') ?></td>
                <td>
                    <?php
                        $tallas = $tallas_map[$product['id']] ?? [];
                        echo implode(', ', $tallas);
                    ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                    <a href="../../controllers/DeleteProduct.php?id=<?= $product['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que querís borrar esta wea?')">🗑️ Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

