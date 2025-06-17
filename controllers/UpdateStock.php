<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$stocks = $_POST['stock'] ?? [];

foreach ($stocks as $ps_id => $valor) {
    $valor = max(0, (int)$valor);
    $stmt = $pdo->prepare("UPDATE product_sizes SET stock = ? WHERE id = ?");
    $stmt->execute([$valor, $ps_id]);
}

$_SESSION['success'] = "✅ Stock actualizado correctamente.";
header("Location: ../views/products/index.php");
exit;
