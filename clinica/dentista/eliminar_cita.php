<?php
// dentista/eliminar_cita.php (Contenido actualizado para FINALIZAR la cita)
session_start(); // Asegúrate de iniciar la sesión aquí si no lo haces en auth_dentista.php
require_once '../config/db.php';
require_once 'auth_dentista.php'; // Necesitas esta dependencia para obtener $dentista_usuario_id
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$cita_id = $data['cita_id'] ?? null;

if (!$cita_id) {
    echo json_encode(["success" => false, "message" => "ID de cita inválido."]);
    exit;
}

try {
    // 1. Obtener el dentista_id para garantizar seguridad (de auth_dentista.php)
    // Asumimos que $dentista_usuario_id está disponible desde el require_once 'auth_dentista.php';
    $stmt_dentista = $pdo->prepare("SELECT dentista_id FROM dentistas WHERE usuario_id = ?");
    $stmt_dentista->execute([$dentista_usuario_id]);
    $dentista_data = $stmt_dentista->fetch(PDO::FETCH_ASSOC);
    
    if (!$dentista_data) {
        throw new Exception("Error de autenticación del dentista.");
    }
    $dentista_id = $dentista_data['dentista_id'];
    
    // 2. CAMBIO CLAVE: Actualizamos el estado a 'Finalizada' en lugar de ELIMINAR
    $sql_update = "UPDATE citas SET estado = 'Finalizada' WHERE cita_id = ? AND dentista_id = ? AND estado = 'Confirmada'";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$cita_id, $dentista_id]);
    
    if ($stmt_update->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Cita marcada como Finalizada."]);
    } else {
        echo json_encode(["success" => false, "message" => "No se pudo finalizar la cita. Podría no estar asignada o ya fue finalizada."]);
    }
    
} catch (Exception $e) {
    // Aquí se capturaría el error 1451 si no lo hubiéramos corregido,
    // pero ahora capturará errores generales.
    echo json_encode(["success" => false, "message" => "Error de procesamiento: " . $e->getMessage()]);
}
?>