<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Récupérer un produit spécifique
                $query = "SELECT * FROM products WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_GET['id']);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    echo json_encode($product);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Produit non trouvé']);
                }
            } else {
                // Récupérer tous les produits
                $query = "SELECT * FROM products ORDER BY created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($products);
            }
            break;

        case 'POST':
            // Créer un nouveau produit
            $data = json_decode(file_get_contents("php://input"), true);
            
            $query = "INSERT INTO products (name, description, price, category, stock, image, status) 
                      VALUES (:name, :description, :price, :category, :stock, :image, :status)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':stock', $data['stock']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['message' => 'Produit créé avec succès', 'id' => $db->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la création du produit']);
            }
            break;

        case 'PUT':
            // Mettre à jour un produit
            $data = json_decode(file_get_contents("php://input"), true);
            
            $query = "UPDATE products SET 
                      name = :name, 
                      description = :description, 
                      price = :price, 
                      category = :category, 
                      stock = :stock, 
                      image = :image, 
                      status = :status 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':stock', $data['stock']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Produit mis à jour avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la mise à jour du produit']);
            }
            break;

        case 'DELETE':
            // Supprimer un produit
            if (isset($_GET['id'])) {
                $query = "DELETE FROM products WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_GET['id']);
                
                if ($stmt->execute()) {
                    echo json_encode(['message' => 'Produit supprimé avec succès']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erreur lors de la suppression du produit']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID du produit requis']);
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