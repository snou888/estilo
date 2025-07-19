<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Récupérer une commande spécifique avec ses détails
                $query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                                 u.email as customer_email,
                                 COUNT(oi.id) as items_count
                          FROM orders o
                          JOIN users u ON o.user_id = u.id
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.id = :id
                          GROUP BY o.id";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_GET['id']);
                $stmt->execute();
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($order) {
                    // Récupérer les articles de la commande
                    $query = "SELECT oi.*, p.name as product_name, p.image as product_image
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.id
                              WHERE oi.order_id = :order_id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':order_id', $_GET['id']);
                    $stmt->execute();
                    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode($order);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Commande non trouvée']);
                }
            } else {
                // Récupérer toutes les commandes
                $query = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                                 u.email as customer_email,
                                 COUNT(oi.id) as items_count
                          FROM orders o
                          JOIN users u ON o.user_id = u.id
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          GROUP BY o.id
                          ORDER BY o.created_at DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute();
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($orders);
            }
            break;

        case 'PUT':
            // Mettre à jour le statut d'une commande
            $data = json_decode(file_get_contents("php://input"), true);
            
            $query = "UPDATE orders SET status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Statut de la commande mis à jour avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la mise à jour du statut']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            break;
    }

} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de base de données: ' . $exception->getMessage()
    ]);
}
?>