<?php
// config/db.php

$host = 'localhost';
$db   = 'clinica'; // CRÍTICO: Asegúrate de que este nombre sea EXACTO.
$user = 'root';    // Usuario de MySQL
$pass = '';        // Contraseña de MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Intenta establecer la conexión
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Error fatal de conexión a la BD: " . $e->getMessage());
    // CRÍTICO: Definir $pdo como null para que el script que lo usa pueda manejarlo
    $pdo = null; 
}
// NOTA: NO hay etiqueta de cierre ?>