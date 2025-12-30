<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM consultas LIKE 'metodo_pago'");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "Column 'metodo_pago' already exists.\n";
    } else {
        // Add column
        $sql = "ALTER TABLE consultas ADD COLUMN metodo_pago VARCHAR(50) NULL AFTER costo_servicio";
        $pdo->exec($sql);
        echo "Successfully added 'metodo_pago' column to 'consultas' table.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
