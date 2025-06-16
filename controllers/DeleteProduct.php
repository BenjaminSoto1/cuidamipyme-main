<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) exit;

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header("Location: ../views/products/index.php");
