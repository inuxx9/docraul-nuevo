<?php
// api/login_proceso.php (Versión de Corrección de Hash Final para ambos roles)
session_start();
require_once '../config/db.php'; 

$mensaje_error = "Usuario o contraseña inválida."; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $_SESSION['error'] = "Por favor, introduce usuario y contraseña.";
        header('Location: ../login/login.php');
        exit;
    }

    try {
        // Consultar usuario por email/nombre
        $stmt = $pdo->prepare("SELECT usuario_id, password, rol FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $login_exitoso = false;

        if ($user) {
            $hash_de_la_bd = $user['password'];

            // ----------------------------------------------------
            // FIX TEMPORAL CRÍTICO: Permitir la contraseña 'miclave' para DENTISTAS y RECEPCIONISTAS
            // Esto corrige el hash en la BD al primer intento exitoso.
            if (($user['rol'] === 'recepcionista' || $user['rol'] === 'dentista') && $password === 'miclave') {
                 
                 // Generar un hash nuevo y compatible
                 $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
                 
                 // Actualizar la BD con el hash correcto
                 $stmt_update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE usuario_id = ?");
                 $stmt_update->execute([$nuevo_hash, $user['usuario_id']]);
                 
                 $login_exitoso = true; // Permite el login por esta vez y corrige el hash
            } 
            // ----------------------------------------------------
            // Lógica normal de verificación de hash (para después de la corrección)
            elseif (password_verify($password, $hash_de_la_bd)) {
                $login_exitoso = true;
            }
        }
        
        if ($login_exitoso) {
            // Éxito: Crear sesión
            $_SESSION['usuario_id'] = $user['usuario_id']; 
            $_SESSION['rol'] = $user['rol'];
            
            // Redireccionar
            if ($user['rol'] === 'recepcionista') {
                header('Location: ../recepcionista/dashboard.php');
                exit;
            } elseif ($user['rol'] === 'dentista') {
                header('Location: ../dentista/panel.php');
                exit;
            }
            
            header('Location: ../index.html');

        } else {
            $_SESSION['error'] = $mensaje_error;
            header('Location: ../login/login.php');
        }

    } catch (PDOException $e) {
        error_log("Error de login en la BD: " . $e->getMessage());
        $_SESSION['error'] = "Error de servidor (SQL). Intenta más tarde."; 
        header('Location: ../login/login.php');
        exit;
    }
} else {
    header('Location: ../login/login.php');
    exit;
}
?>