<?php
session_start();
require_once '../../config/database.php';
$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM sales WHERE user_id = ? ORDER BY sale_date DESC");
$stmt->execute([$user_id]);
$sales = $stmt->fetchAll();
?>

<h2>Mis Ventas</h2>
<a href="../../controllers/ExportSalesCSV.php">⬇️ Exportar a Excel (CSV)</a>

<a href="create.php">➕ Registrar nueva venta</a>
<table border="1" cellpadding="5">
    <tr><th>Cliente</th><th>Fecha</th><th>Total</th></tr>
    <?php foreach ($sales as $sale): ?>
        <tr>
            <td><?= htmlspecialchars($sale['client_name']) ?></td>
            <td><?= $sale['sale_date'] ?></td>
            <td>
    <a href="show.php?id=<?= $sale['id'] ?>">
        Ver detalle
    </a> ($<?= number_format($sale['total'], 0, ',', '.') ?>)
</td>


        </tr>
    <?php endforeach; ?>
</table>
