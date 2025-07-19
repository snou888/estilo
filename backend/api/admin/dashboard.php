<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Statistiques générales
    $stats = [];

    // Total des utilisateurs
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalUsers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total des produits
    $query = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalProducts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total des commandes
    $query = "SELECT COUNT(*) as total FROM orders";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalOrders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Revenus totaux
    $query = "SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE status != 'cancelled'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalRevenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'];

    // Commandes récentes
    $query = "SELECT o.id, CONCAT(u.first_name, ' ', u.last_name) as customerName, 
                     o.total, o.status, o.created_at as date
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['recentOrders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Produits populaires
    $query = "SELECT p.id, p.name, 
                     COUNT(oi.product_id) as sales,
                     SUM(oi.price * oi.quantity) as revenue
              FROM products p
              JOIN order_items oi ON p.id = oi.product_id
              JOIN orders o ON oi.order_id = o.id
              WHERE o.status != 'cancelled'
              GROUP BY p.id, p.name
              ORDER BY sales DESC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['topProducts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stats);

} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de base de données: ' . $exception->getMessage()
    ]);
}
?>