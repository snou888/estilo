@@ .. @@
 -- Ajout de colonnes pour l'administration
 ALTER TABLE products ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active';
 ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
 ALTER TABLE products ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

 ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active';
 ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL;
+
+-- Table pour les commandes
+CREATE TABLE IF NOT EXISTS orders (
+    id INT AUTO_INCREMENT PRIMARY KEY,
+    user_id INT NOT NULL,
+    total DECIMAL(10,2) NOT NULL,
+    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
+    shipping_address TEXT NOT NULL,
+    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
+    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
+    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
+);
+
+-- Table pour les articles de commande
+CREATE TABLE IF NOT EXISTS order_items (
+    id INT AUTO_INCREMENT PRIMARY KEY,
+    order_id INT NOT NULL,
+    product_id INT NOT NULL,
+    quantity INT NOT NULL,
+    price DECIMAL(10,2) NOT NULL,
+    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
+    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
+    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
+);
+
+-- Données de test pour les commandes
+INSERT INTO orders (user_id, total, status, shipping_address) VALUES
+(1, 159.97, 'delivered', '123 Rue de la Paix, 75001 Paris'),
+(2, 79.99, 'shipped', '456 Avenue des Champs, 69000 Lyon'),
+(1, 29.99, 'processing', '123 Rue de la Paix, 75001 Paris'),
+(3, 209.98, 'pending', '789 Boulevard Saint-Germain, 33000 Bordeaux');
+
+INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
+(1, 1, 2, 29.99),
+(1, 3, 1, 129.99),
+(2, 2, 1, 79.99),
+(3, 1, 1, 29.99),
+(4, 2, 1, 79.99),
+(4, 3, 1, 129.99);