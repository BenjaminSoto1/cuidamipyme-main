<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/database.php';

$user_id = $_SESSION['user']['id'];

// Obtener productos
$stmt = $pdo->prepare("SELECT p.*, b.name as brand_name, c.name as category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.user_id = ?");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll();

// Obtener stock bajo desde product_sizes
$alerta_stmt = $pdo->prepare("
    SELECT p.name, s.label, ps.stock
    FROM product_sizes ps
    JOIN products p ON ps.product_id = p.id
    JOIN sizes s ON ps.size_id = s.id
    WHERE p.user_id = ? AND ps.stock <= 5
");
$alerta_stmt->execute([$user_id]);
$stock_bajo = $alerta_stmt->fetchAll();

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

// Obtener ventas recientes
$stmt = $pdo->prepare("SELECT * FROM sales WHERE user_id = ? ORDER BY sale_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

    <h2 class="mb-4">Bienvenido, <?= $_SESSION['user']['name'] ?> 👋</h2>

    <?php if (count($stock_bajo) > 0): ?>
        <div class="alert alert-danger">
            <strong>⚠️ Stock bajo detectado:</strong>
            <ul class="mb-0">
                <?php foreach ($stock_bajo as $item): ?>
                    <li><?= htmlspecialchars($item['name']) ?> - Talla <?= $item['label'] ?> (<?= $item['stock'] ?> unidades)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h3 class="mt-5">📊 Ventas Últimos Días</h3>
    <div style="max-width: 600px; margin: auto;">
      <canvas id="ventasChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    fetch('../controllers/SalesData.php')
      .then(res => res.json())
      .then(data => {
        const fechas = data.map(row => row.date);
        const totales = data.map(row => row.total);

        const ctx = document.getElementById('ventasChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.6)');
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.05)');

        new Chart(ctx, {
          type: 'line',
          data: {
            labels: fechas,
            datasets: [{
              label: 'Total Vendido ($)',
              data: totales,
              fill: true,
              backgroundColor: gradient,
              borderColor: 'rgba(54, 162, 235, 1)',
              pointBackgroundColor: 'rgba(54, 162, 235, 1)',
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: value => `$${value.toLocaleString('es-CL')}`
                }
              }
            },
            plugins: {
              legend: { position: 'top' },
              tooltip: {
                callbacks: {
                  label: ctx => `$${ctx.parsed.y.toLocaleString('es-CL')}`
                }
              }
            }
          }
        });
      })
      .catch(err => console.error('💥 ERROR:', err));
    </script>

    <hr class="my-5">
    <h3>🛒 Mis Productos</h3>
    <a href="products/create.php" class="btn btn-success btn-sm mb-3">➕ Agregar Producto</a>
    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Tallas (Stock)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['brand_name']) ?></td>
                <td><?= htmlspecialchars($product['category_name']) ?></td>
                <td>$<?= number_format($product['price'], 0, ',', '.') ?></td>
                <td><?= implode(', ', $tallas_map[$product['id']] ?? []) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr class="my-5">
    <h3>💰 Últimas Ventas</h3>
    <a href="sales/create.php" class="btn btn-primary btn-sm mb-2">➕ Registrar Venta</a>
    <a href="sales/index.php" class="btn btn-outline-secondary btn-sm mb-2">📄 Ver todas</a>
    <table class="table table-striped table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['client_name']) ?></td>
                <td><?= $s['sale_date'] ?></td>
                <td>$<?= number_format($s['total'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="../logout.php" class="btn btn-danger btn-sm mt-3">🔚 Cerrar sesión</a>

</div>
</body>
</html>
