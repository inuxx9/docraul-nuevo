<?php
// recepcionista/asignar_dentista.php
session_start();

// CRÍTICO: Revisar la ruta de autenticación y conexión
require_once '../config/db.php'; 

// Verifica que la variable $pdo esté disponible
if (!isset($pdo) || !$pdo instanceof PDO) {
    // Si la conexión falló, redirigir o mostrar error
    header('Location: dashboard.php?error=Error de conexión con la BD.');
    exit;
}

// 1. Verificar el método de la solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// 2. Extracción y saneamiento de datos
$cita_id = filter_input(INPUT_POST, 'cita_id', FILTER_VALIDATE_INT);
$dentista_id = filter_input(INPUT_POST, 'dentista_id', FILTER_VALIDATE_INT);

// 3. Validación de datos
if (empty($cita_id) || empty($dentista_id)) {
    // Redireccionar con el mensaje de error que estabas viendo
    header('Location: dashboard.php?error=Datos de cita o dentista incompletos.');
    exit;
}

// 4. Procesamiento de asignación
try {
    // SQL: Actualizar la cita con el dentista asignado y cambiar el estado
    $sql = "UPDATE citas SET dentista_id = ?, estado = 'Confirmada' WHERE cita_id = ? AND estado = 'Pendiente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dentista_id, $cita_id]);

    // 5. Respuesta y redirección
    if ($stmt->rowCount() > 0) {
        header('Location: dashboard.php?success=Cita ' . $cita_id . ' asignada y confirmada con éxito.');
        exit;
    } else {
        header('Location: dashboard.php?error=No se pudo asignar la cita. Puede que ya esté confirmada.');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error al asignar dentista: " . $e->getMessage());
    header('Location: dashboard.php?error=Error de servidor al procesar la asignación.');
    exit;
}
?>