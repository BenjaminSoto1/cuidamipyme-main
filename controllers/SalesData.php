<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) exit;

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(sale_date, '%Y-%m-%d') as date, SUM(total) as total
    FROM sales
    WHERE user_id = ?
    GROUP BY DATE(sale_date)
    ORDER BY DATE(sale_date) ASC
    LIMIT 7
");
$stmt->execute([$user_id]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
exit;
