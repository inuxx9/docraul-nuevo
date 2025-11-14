<?php
// test_connection.php
require_once 'config/db.php'; 

if (isset($pdo) && $pdo instanceof PDO) {
    echo "✅ Conexión a la base de datos 'clinica' establecida con éxito.";
} else {
    echo "❌ Fallo al cargar la variable \$pdo.";
}
?>