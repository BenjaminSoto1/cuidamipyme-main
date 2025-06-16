<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$sale_id = $_GET['id'] ?? null;

// Obtener la venta
$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? AND user_id = ?");
$stmt->execute([$sale_id, $user_id]);
$sale = $stmt->fetch();

if (!$sale) {
    echo "Venta no encontrada.";
    exit;
}

// Obtener detalle de la venta con talla
$stmt = $pdo->prepare("
    SELECT si.*, p.name AS product_name, s.label AS size_label
FROM sale_items si
JOIN products p ON si.product_id = p.id
LEFT JOIN sizes s ON si.size_id = s.id
WHERE si.sale_id = ?
");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">🧾 Detalle de Venta #<?= $sale['id'] ?></h2>

    <p><strong>Cliente:</strong> <?= htmlspecialchars($sale['client_name']) ?></p>
    <p><strong>Fecha:</strong> <?= $sale['sale_date'] ?></p>
    <p><strong>Total:</strong> $<?= number_format($sale['total'], 0, ',', '.') ?></p>

    <h5 class="mt-4">Productos Vendidos:</h5>
    <table class="table table-bordered table-sm mt-2">
        <thead class="table-light">
            <tr>
                <th>Producto</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['size_label'] ?? '—' ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price_unit'], 0, ',', '.') ?></td>
                <td>$<?= number_format($item['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn btn-secondary btn-sm">← Volver</a>
</div>
</body>
</html>
