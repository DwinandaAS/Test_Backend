<?php
// Setup SQLite database for testing

$dbPath = __DIR__ . '/db_test.sqlite';

echo "Creating SQLite database...\n";

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✓ Users table created\n";
    
    // Insert test data with bcrypt hashed passwords
    // testuser / testpass123
    // password hash generated with password_hash('testpass123', PASSWORD_BCRYPT)
    $testPassword = password_hash('testpass123', PASSWORD_BCRYPT);
    $testPassword2 = password_hash('password123', PASSWORD_BCRYPT);
    
    $db->exec("DELETE FROM users");
    
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['testuser', $testPassword, 'admin']);
    echo "✓ User 'testuser' created with password 'testpass123'\n";
    
    $stmt->execute(['john_doe', $testPassword2, 'user']);
    echo "✓ User 'john_doe' created with password 'password123'\n";
    
    echo "\n✓ Database setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    die(1);
}
