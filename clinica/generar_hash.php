<?php
// generar_hash.php
$contrasena = 'Recep2025'; // La contraseña real
echo "HASH GENERADO para '{$contrasena}': " . password_hash($contrasena, PASSWORD_BCRYPT);
?>