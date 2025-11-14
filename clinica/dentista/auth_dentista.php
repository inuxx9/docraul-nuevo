<?php
// dentista/auth_dentista.php

// 🛠️ CORRECCIÓN: Iniciar la sesión solo si no hay una sesión activa.
// Esto elimina la advertencia de 'session_start() already active'.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si la sesión existe y si el rol es 'dentista'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'dentista') {
    // Si no cumple, redirige al login
    header('Location: ../login/login.php'); 
    exit;
}

// Si la verificación pasa, el script continúa
// Se asume que $_SESSION['usuario_id'] contiene el ID del usuario logueado
$dentista_usuario_id = $_SESSION['usuario_id'];
?>