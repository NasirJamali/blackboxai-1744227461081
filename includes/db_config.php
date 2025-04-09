<?php
$host = 'localhost';
$dbname = 'building_materials_market';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $conn->exec("CREATE TABLE IF NOT EXISTS tenders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        buyer_id INT NOT NULL,
        material_name VARCHAR(100) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        unit VARCHAR(20) NOT NULL,
        notes TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (buyer_id) REFERENCES users(id)
    )");
    
    $conn->exec("CREATE TABLE IF NOT EXISTS offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tender_id INT NOT NULL,
        seller_id INT NOT NULL,
        price_per_unit DECIMAL(10,2) NOT NULL,
        delivery_time VARCHAR(50) NOT NULL,
        contact_info VARCHAR(100) NOT NULL,
        message TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tender_id) REFERENCES tenders(id),
        FOREIGN KEY (seller_id) REFERENCES users(id)
    )");
    
    // Check if admin user exists
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE email='admin@example.com'");
    if($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO users (username, email, password, role) 
                    VALUES ('admin', 'admin@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
    }
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
