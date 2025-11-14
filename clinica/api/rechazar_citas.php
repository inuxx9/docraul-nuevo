<?php
// api/rechazar_cita.php
session_start();
require_once '../config/db.php'; 

// 1. Verificar Rol y Método
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'recepcionista' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "❌ Acceso no autorizado.";
    header('Location: ../login/login.php');
    exit;
}

// 2. Extracción de Datos
$cita_id = $_POST['cita_id'] ?? null;
$accion = $_POST['accion'] ?? null; 

// Asegurar que se recibió el ID de la cita y la acción es 'rechazar'
if (empty($cita_id) || $accion !== 'rechazar') {
    $_SESSION['error'] = "❌ Error: Datos incompletos para rechazar la cita.";
    header('Location: ../recepcionista/dashboard.php');
    exit;
}

try {
    // 3. Procesar Rechazo en la BD
    // Establece el estado a 'Rechazada' y el dentista_id a NULL
    $sql = "UPDATE citas SET estado = 'Rechazada', dentista_id = NULL WHERE cita_id = ? AND estado = 'Pendiente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cita_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "❌ Cita ID {$cita_id} ha sido **Rechazada** con éxito.";
    } else {
        $_SESSION['error'] = "⚠️ La cita no pudo ser rechazada (puede que ya no esté pendiente).";
    }

} catch (PDOException $e) {
    error_log("Error al rechazar cita: " . $e->getMessage());
    $_SESSION['error'] = "❌ Error de servidor: No se pudo rechazar la cita.";
}

// 4. Redirigir al panel
header('Location: ../recepcionista/dashboard.php');
exit;