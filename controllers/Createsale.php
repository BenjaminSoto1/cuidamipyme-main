<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$client_name = trim($_POST['client_name'] ?? '');
$product_id = $_POST['product_id'] ?? null;
$size_id = $_POST['size_id'] ?? null;
$quantity = max(1, (int)($_POST['quantity'] ?? 0));

if (!$client_name || !$product_id || !$size_id || $quantity <= 0) {
    die("Faltan datos obligatorios.");
}

// Obtener precio y stock actual
$stmt = $pdo->prepare("
    SELECT p.price, ps.stock 
    FROM products p
    JOIN product_sizes ps ON ps.product_id = p.id
    WHERE p.id = ? AND ps.size_id = ? AND p.user_id = ?
");
$stmt->execute([$product_id, $size_id, $user_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Producto o talla no válidos.");
}

if ($data['stock'] < $quantity) {
    die("No hay suficiente stock para esta talla.");
}

$subtotal = $data['price'] * $quantity;

// Registrar venta
$stmt = $pdo->prepare("INSERT INTO sales (user_id, client_name, total) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $client_name, $subtotal]);
$sale_id = $pdo->lastInsertId();

// Registrar detalle
$stmt = $pdo->prepare("
    INSERT INTO sale_items (sale_id, product_id, size_id, quantity, price_unit, subtotal)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$sale_id, $product_id, $size_id, $quantity, $data['price'], $subtotal]);


// Descontar stock
$stmt = $pdo->prepare("
    UPDATE product_sizes SET stock = stock - ? WHERE product_id = ? AND size_id = ?
");
$stmt->execute([$quantity, $product_id, $size_id]);

header("Location: ../views/sales/index.php");
exit;
