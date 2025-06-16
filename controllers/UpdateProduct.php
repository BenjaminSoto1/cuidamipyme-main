<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;
$brand_id = $_POST['brand_id'] ?? null;
$category_id = $_POST['category_id'] ?? null;

// Validación básica
if (!$id || empty($name) || $price <= 0 || !$brand_id || !$category_id) {
    die("Faltan datos obligatorios o inválidos.");
}

// Verificar que el producto exista y sea del usuario
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
if (!$stmt->fetch()) {
    die("Producto no encontrado.");
}

// Actualizar producto
$stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, brand_id = ?, category_id = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$name, $price, $brand_id, $category_id, $id, $user_id]);

// Limpiar tallas anteriores
$stmt = $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?");
$stmt->execute([$id]);

// Insertar nuevas tallas si vienen
if (isset($_POST['sizes']) && isset($_POST['stock'])) {
    foreach ($_POST['sizes'] as $size_id => $checked) {
        $stock = $_POST['stock'][$size_id] ?? 0;
        $stock = max(0, (int)$stock);

        if ($stock > 0) {
            $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)");
            $stmt->execute([$id, $size_id, $stock]);
        }
    }
}

header("Location: ../views/products/index.php");
exit;
