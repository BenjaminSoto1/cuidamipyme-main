<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;
$nueva_categoria = trim($_POST['nueva_categoria'] ?? '');
$nueva_talla = trim($_POST['nueva_talla'] ?? '');
$nueva_talla_stock = max(0, (int)($_POST['nueva_talla_stock'] ?? 0));
$category_id = $_POST['category_id'] ?? null;

$brand_id = $_POST['brand_id'] ?? null;
$nueva_marca = trim($_POST['nueva_marca'] ?? '');

// 💡 Si se escribió una nueva marca, insertarla
if ($nueva_marca !== '') {
    $stmt = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
    $stmt->execute([$nueva_marca]);
    $brand_id = $pdo->lastInsertId(); // Sobrescribe brand_id
}

// Si se escribió una nueva categoría, insertarla
if ($nueva_categoria !== '') {
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$nueva_categoria]);
    $category_id = $pdo->lastInsertId(); // Sobrescribe category_id
}


// Validación básica
if (empty($name) || $price <= 0 || !$brand_id || !$category_id) {
    die("Faltan datos obligatorios.");
}


// Insertar producto
$stmt = $pdo->prepare("INSERT INTO products (user_id, name, price, brand_id, category_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $name, $price, $brand_id, $category_id]);
$product_id = $pdo->lastInsertId();

// Si se escribió una nueva talla, insertarla
// Múltiples tallas manuales
$tallas = $_POST['tallas'] ?? [];
$stocks = $_POST['stocks'] ?? [];

foreach ($tallas as $i => $nombre_talla) {
    $nombre_talla = trim($nombre_talla);
    $stock = max(0, (int)($stocks[$i] ?? 0));

    if ($nombre_talla !== '' && $stock > 0) {
        // Insertar talla si no existe aún
        $stmt = $pdo->prepare("INSERT INTO sizes (label) VALUES (?)");
        $stmt->execute([$nombre_talla]);
        $talla_id = $pdo->lastInsertId();

        // Asignar a producto
        $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $talla_id, $stock]);
    }
}


// Guardar tallas y stock si vienen
if (isset($_POST['sizes']) && isset($_POST['stock'])) {
    foreach ($_POST['sizes'] as $size_id => $checked) {
        $stock = $_POST['stock'][$size_id] ?? 0;
        $stock = max(0, (int)$stock);
        if ($stock > 0) {
            $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $size_id, $stock]);
        }
    }
}

header("Location: ../views/products/index.php");
exit;
