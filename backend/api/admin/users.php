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
                // Récupérer un utilisateur spécifique
                $query = "SELECT u.*, 
                                 COUNT(DISTINCT o.id) as orders_count,
                                 COALESCE(SUM(o.total), 0) as total_spent
                          FROM users u
                          LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
                          WHERE u.id = :id
                          GROUP BY u.id";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_GET['id']);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Ne pas retourner le mot de passe
                    unset($user['password']);
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Utilisateur non trouvé']);
                }
            } else {
                // Récupérer tous les utilisateurs
                $query = "SELECT u.*, 
                                 COUNT(DISTINCT o.id) as orders_count,
                                 COALESCE(SUM(o.total), 0) as total_spent
                          FROM users u
                          LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
                          GROUP BY u.id
                          ORDER BY u.created_at DESC";
                
                $stmt = $db->prepare($query);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Ne pas retourner les mots de passe
                foreach ($users as &$user) {
                    unset($user['password']);
                }
                
                echo json_encode($users);
            }
            break;

        case 'POST':
            // Créer un nouvel utilisateur
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Vérifier si l'email existe déjà
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Cet email est déjà utilisé']);
                break;
            }
            
            $query = "INSERT INTO users (first_name, last_name, email, password, role, status) 
                      VALUES (:first_name, :last_name, :email, :password, :role, :status)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['message' => 'Utilisateur créé avec succès', 'id' => $db->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la création de l\'utilisateur']);
            }
            break;

        case 'PUT':
            // Mettre à jour un utilisateur
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (isset($data['password']) && !empty($data['password'])) {
                // Mise à jour avec mot de passe
                $query = "UPDATE users SET 
                          first_name = :first_name, 
                          last_name = :last_name, 
                          email = :email, 
                          password = :password,
                          role = :role, 
                          status = :status 
                          WHERE id = :id";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            } else {
                // Mise à jour sans mot de passe
                $query = "UPDATE users SET 
                          first_name = :first_name, 
                          last_name = :last_name, 
                          email = :email, 
                          role = :role, 
                          status = :status 
                          WHERE id = :id";
                
                $stmt = $db->prepare($query);
            }
            
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Utilisateur mis à jour avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la mise à jour de l\'utilisateur']);
            }
            break;

        case 'DELETE':
            // Supprimer un utilisateur
            if (isset($_GET['id'])) {
                // Vérifier s'il y a des commandes associées
                $query = "SELECT COUNT(*) as count FROM orders WHERE user_id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_GET['id']);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['count'] > 0) {
                    // Désactiver l'utilisateur au lieu de le supprimer
                    $query = "UPDATE users SET status = 'inactive' WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $_GET['id']);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['message' => 'Utilisateur désactivé (commandes existantes)']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Erreur lors de la désactivation']);
                    }
                } else {
                    // Supprimer l'utilisateur
                    $query = "DELETE FROM users WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $_GET['id']);
                    
                    if ($stmt->execute()) {
                        echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Erreur lors de la suppression']);
                    }
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID de l\'utilisateur requis']);
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