<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) exit;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ventas.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Cliente', 'Fecha', 'Total']);

$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM sales WHERE user_id = ? ORDER BY sale_date DESC");
$stmt->execute([$user_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [$row['id'], $row['client_name'], $row['sale_date'], $row['total']]);
}

fclose($output);
exit;
