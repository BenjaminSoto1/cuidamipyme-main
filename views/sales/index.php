<?php
session_start();
require_once('../../config/database.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM sales WHERE user_id = ? ORDER BY sale_date DESC");
$stmt->execute([$user_id]);
$sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">🧾 Mis Ventas</h2>

    <div class="mb-3">
        <a href="../../controllers/ExportSalesCSV.php" class="btn btn-outline-success btn-sm">
            ⬇️ Exportar a CSV
        </a>
        <a href="create.php" class="btn btn-primary btn-sm">
            ➕ Registrar nueva venta
        </a>
    </div>

    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light">
            <tr>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?= htmlspecialchars($sale['client_name']) ?></td>
                <td><?= $sale['sale_date'] ?></td>
                <td>$<?= number_format($sale['total'], 0, ',', '.') ?></td>
                <td>
                    <a href="show.php?id=<?= $sale['id'] ?>" class="btn btn-info btn-sm">
                        Ver detalle
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="../dashboard.php" class="btn btn-secondary btn-sm mt-3">
        ← Volver al Inicio
    </a>
</div>
</body>
</html>